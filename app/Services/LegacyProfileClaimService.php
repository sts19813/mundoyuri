<?php

namespace App\Services;

use App\Models\LegacyProfile;
use App\Models\LegacyProfileClaim;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LegacyProfileClaimService
{
    public function submit(LegacyProfile $legacyProfile, User $claimant, string $message, ?string $evidence): LegacyProfileClaim
    {
        return DB::transaction(function () use ($legacyProfile, $claimant, $message, $evidence): LegacyProfileClaim {
            $legacyProfile = LegacyProfile::query()->lockForUpdate()->findOrFail($legacyProfile->id);

            if (! $legacyProfile->is_published || ! in_array($legacyProfile->claim_status, ['unclaimed', 'rejected'], true)) {
                throw ValidationException::withMessages(['legacy_profile_id' => 'Este perfil histórico no está disponible para reclamación.']);
            }

            if (LegacyProfileClaim::query()
                ->where('legacy_profile_id', $legacyProfile->id)
                ->where('claimant_user_id', $claimant->id)
                ->exists()) {
                throw ValidationException::withMessages(['legacy_profile_id' => 'Ya enviaste una solicitud para este perfil histórico.']);
            }

            $claim = $legacyProfile->claims()->create([
                'claimant_user_id' => $claimant->id,
                'message' => $message,
                'evidence' => $evidence,
            ]);

            $legacyProfile->update(['claim_status' => 'pending']);

            return $claim;
        });
    }

    public function review(LegacyProfileClaim $claim, User $reviewer, string $status, ?string $adminNotes): void
    {
        DB::transaction(function () use ($claim, $reviewer, $status, $adminNotes): void {
            $claim = LegacyProfileClaim::query()->lockForUpdate()->findOrFail($claim->id);

            if ($claim->status !== 'pending') {
                throw ValidationException::withMessages(['claim' => 'Esta solicitud ya fue revisada.']);
            }

            $legacyProfile = LegacyProfile::query()->lockForUpdate()->findOrFail($claim->legacy_profile_id);

            if ($status === 'approved') {
                $this->approve($claim, $legacyProfile, $reviewer, $adminNotes);

                return;
            }

            $claim->update([
                'status' => 'rejected',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'admin_notes' => $adminNotes,
            ]);

            if ($legacyProfile->claim_status !== 'claimed') {
                $legacyProfile->update(['claim_status' => 'rejected']);
            }

            $this->log($legacyProfile, $reviewer, 'legacy_profile_claim_rejected', $claim);
        });
    }

    private function approve(LegacyProfileClaim $claim, LegacyProfile $legacyProfile, User $reviewer, ?string $adminNotes): void
    {
        if ($legacyProfile->claim_status === 'claimed' || LegacyProfileClaim::query()
            ->where('legacy_profile_id', $legacyProfile->id)
            ->where('status', 'approved')
            ->exists()) {
            throw ValidationException::withMessages(['claim' => 'Este perfil histórico ya fue reclamado por otra cuenta.']);
        }

        $claimant = User::query()->lockForUpdate()->findOrFail($claim->claimant_user_id);

        if ($claimant->profile_claimed_at || $claimant->claimedLegacyProfiles()->where('claim_status', 'claimed')->exists()) {
            throw ValidationException::withMessages(['claim' => 'Esta cuenta ya tiene un perfil histórico reclamado.']);
        }

        $claimedAt = now();
        $claim->update([
            'status' => 'approved',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => $claimedAt,
            'admin_notes' => $adminNotes,
        ]);
        $legacyProfile->update([
            'claim_status' => 'claimed',
            'claimed_by_user_id' => $claimant->id,
            'claimed_at' => $claimedAt,
        ]);

        // Solo se sincronizan metadatos históricos explícitos; nunca credenciales ni datos de perfil actuales.
        $claimant->update([
            'is_legacy' => true,
            'legacy_joined_at' => $legacyProfile->legacy_joined_at,
            'legacy_source' => $legacyProfile->source,
            'legacy_verified' => $legacyProfile->legacy_verified,
            'profile_claimed_at' => $claimedAt,
        ]);

        $this->log($legacyProfile, $reviewer, 'legacy_profile_claim_approved', $claim);
    }

    private function log(LegacyProfile $legacyProfile, User $reviewer, string $action, LegacyProfileClaim $claim): void
    {
        $legacyProfile->moderationLogs()->create([
            'actor_id' => $reviewer->id,
            'action' => $action,
            'metadata' => [
                'legacy_profile_claim_id' => $claim->id,
                'claimant_user_id' => $claim->claimant_user_id,
                'status' => $claim->status,
            ],
        ]);
    }
}
