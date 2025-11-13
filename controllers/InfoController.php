<?php
namespace App\Controllers;

use App\Models\Setting;

final class InfoController extends BaseController
{
    /** Front: lấy thông tin cơ sở (địa chỉ, giờ mở cửa, social) */
    public function frontInfo(): array {
        $s = Setting::one();
        return [
            'restaurant' => $s,
            'social'     => $s && !empty($s['social_links'])
                ? (json_decode($s['social_links'], true) ?: [])
                : []
        ];
    }
}
