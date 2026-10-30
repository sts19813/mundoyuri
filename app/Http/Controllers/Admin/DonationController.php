<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;

class DonationController extends Controller
{
    public function index()
    {
        $donations = Donation::with('user')->latest()->paginate(30);
        $successful = Donation::where('status', 'succeeded');
        $stats = ['attempts' => Donation::count(), 'successful' => (clone $successful)->count(), 'gross' => (clone $successful)->sum('amount'), 'refunded' => (clone $successful)->sum('refunded_amount'), 'fees' => (clone $successful)->where('balance_currency', 'mxn')->sum('stripe_fee'), 'missing_fees' => (clone $successful)->whereNull('stripe_fee')->count()];
        return view('admin.donations.index', compact('donations', 'stats'));
    }
}
