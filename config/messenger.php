<?php

return [
    'attachment_max_kilobytes' => 200 * 1024,
    // GIFs stay animated, so stickers have a tighter limit than chat attachments.
    'sticker_max_kilobytes' => 5 * 1024,
    'temporary_attachment_threshold_bytes' => 20 * 1024 * 1024,
    'temporary_attachment_retention_days' => 30,
];
