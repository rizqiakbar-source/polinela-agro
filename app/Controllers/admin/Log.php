<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;

class Log extends BaseController
{
    public function index()
    {
        $logModel = new ActivityLogModel();
        $logs = $logModel->getLogsWithUser(100);

        $data = [
            'title' => 'Log Aktivitas & Audit Trail - Polinela Agro Digital',
            'logs'  => $logs,
        ];

        return view('admin/log/index', $data);
    }
}
