<?php

if (!function_exists('staff_e')) {
    function staff_e($text): string
    {
        return htmlspecialchars((string)($text ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('staff_format_date')) {
    function staff_format_date(?string $date): string
    {
        return $date ? date('d/m/Y', strtotime($date)) : '--';
    }
}

if (!function_exists('staff_time_ago')) {
    function staff_time_ago(?string $datetime): string
    {
        if (!$datetime) {
            return '--';
        }

        $diff = time() - strtotime($datetime);

        if ($diff < 60) {
            return 'Vừa xong';
        }
        if ($diff < 3600) {
            return floor($diff / 60) . ' phút trước';
        }
        if ($diff < 86400) {
            return floor($diff / 3600) . ' giờ trước';
        }

        return floor($diff / 86400) . ' ngày trước';
    }
}

if (!function_exists('staff_status_label')) {
    function staff_status_label(?string $status): string
    {
        return match ($status) {
            'Pending' => 'Chưa bắt đầu',
            'In Progress' => 'Đang thực hiện',
            'Paused' => 'Tạm dừng',
            'Review' => 'Chờ duyệt',
            'Rejected' => 'Bị từ chối',
            'Completed' => 'Hoàn thành',
            default => 'Chưa rõ',
        };
    }
}

if (!function_exists('staff_status_class')) {
    function staff_status_class(?string $status): string
    {
        return match ($status) {
            'Completed' => 'success',
            'In Progress' => 'primary',
            'Paused' => 'secondary',
            'Review' => 'warning',
            'Rejected' => 'danger',
            default => 'muted',
        };
    }
}

if (!function_exists('staff_priority_label')) {
    function staff_priority_label(?string $priority): string
    {
        return match ($priority) {
            'Critical' => 'Khẩn cấp',
            'High' => 'Cao',
            'Medium' => 'Trung bình',
            'Low' => 'Thấp',
            default => 'Chưa rõ',
        };
    }
}

if (!function_exists('staff_priority_class')) {
    function staff_priority_class(?string $priority): string
    {
        return match ($priority) {
            'Critical' => 'danger',
            'High' => 'orange',
            'Medium' => 'primary',
            'Low' => 'success',
            default => 'muted',
        };
    }
}

if (!function_exists('staff_days_remaining_label')) {
    function staff_days_remaining_label(?string $dueDate): array
    {
        if (!$dueDate) {
            return ['Không có hạn', 'muted'];
        }

        $days = (int)ceil((strtotime($dueDate) - strtotime(date('Y-m-d'))) / 86400);

        if ($days < 0) {
            return [abs($days) . ' ngày quá hạn', 'danger'];
        }
        if ($days === 0) {
            return ['Hạn hôm nay', 'orange'];
        }

        return ["Còn {$days} ngày", $days <= 2 ? 'orange' : 'success'];
    }
}
