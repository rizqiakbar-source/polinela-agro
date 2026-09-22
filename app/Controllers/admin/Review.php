<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\ReviewModel;

class Review extends BaseController
{
    protected $reviewModel;

    public function __construct()
    {
        $this->reviewModel = new ReviewModel();
    }

    public function index()
    {
        $status = $this->request->getGet('status');
        $reviews = $this->reviewModel->getAllReviewsWithDetails($status);

        $data = [
            'title'          => 'Moderasi Ulasan & Rating - Polinela Agro Digital',
            'reviews'        => $reviews,
            'current_status' => $status,
        ];

        return view('admin/review/index', $data);
    }

    public function updateStatus($id)
    {
        $status = $this->request->getPost('status');
        $reply  = trim($this->request->getPost('reply') ?? '');

        $this->reviewModel->update($id, [
            'status'     => $status,
            'reply'      => $reply ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Status ulasan berhasil diperbarui.');
    }

    public function delete($id)
    {
        $this->reviewModel->delete($id);
        return redirect()->back()->with('success', 'Ulasan berhasil dihapus.');
    }
}
