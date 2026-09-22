<?php

namespace App\Controllers;

use App\Models\SusResponseModel;
use App\Libraries\SusCalculator;

class Sus extends BaseController
{
    protected $susModel;

    public function __construct()
    {
        $this->susModel = new SusResponseModel();
    }

    public function index()
    {
        $user = current_user();

        $data = [
            'title'        => 'Kuesioner Evaluasi Usability (SUS) - Polinela Agro Digital',
            'user'         => $user,
            'has_surveyed' => false,
        ];

        if ($user) {
            $existing = $this->susModel->where('user_id', $user['id'])->first();
            if ($existing) {
                $data['has_surveyed'] = true;
                $data['my_score'] = $existing;
            }
        }

        return view('frontend/sus_kuesioner', $data);
    }

    public function submit()
    {
        $answers = [
            'q1'  => (int) $this->request->getPost('q1'),
            'q2'  => (int) $this->request->getPost('q2'),
            'q3'  => (int) $this->request->getPost('q3'),
            'q4'  => (int) $this->request->getPost('q4'),
            'q5'  => (int) $this->request->getPost('q5'),
            'q6'  => (int) $this->request->getPost('q6'),
            'q7'  => (int) $this->request->getPost('q7'),
            'q8'  => (int) $this->request->getPost('q8'),
            'q9'  => (int) $this->request->getPost('q9'),
            'q10' => (int) $this->request->getPost('q10'),
        ];

        // Validasi semua pertanyaan 1-5
        foreach ($answers as $k => $v) {
            if ($v < 1 || $v > 5) {
                return redirect()->back()->withInput()->with('error', 'Mohon jawab semua 10 pertanyaan kuesioner dengan skala 1 sampai 5.');
            }
        }

        $calc = $this->susModel->calculateScore($answers);

        $user = current_user();
        $namaResponden = $user ? $user['nama'] : trim($this->request->getPost('nama_responden') ?: 'Responden Anonim');
        $roleResponden = $user ? $user['role'] : trim($this->request->getPost('role_responden') ?: 'Civitas / Umum');

        $this->susModel->insert([
            'user_id'         => $user ? $user['id'] : null,
            'nama_responden'  => $namaResponden,
            'role_responden'  => $roleResponden,
            'q1'              => $answers['q1'],
            'q2'              => $answers['q2'],
            'q3'              => $answers['q3'],
            'q4'              => $answers['q4'],
            'q5'              => $answers['q5'],
            'q6'              => $answers['q6'],
            'q7'              => $answers['q7'],
            'q8'              => $answers['q8'],
            'q9'              => $answers['q9'],
            'q10'             => $answers['q10'],
            'total_score'     => $calc['total_score'],
            'kategori'        => $calc['kategori'],
            'feedback'        => trim($this->request->getPost('feedback') ?? ''),
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        log_system_activity('Pengisian SUS', 'Evaluasi', "Responden {$namaResponden} mengisi kuesioner SUS (Skor: {$calc['total_score']})");

        return redirect()->to(base_url('sus'))->with('success', "Terima kasih atas partisipasi Anda! Skor evaluasi SUS yang Anda berikan adalah {$calc['total_score']} ({$calc['kategori']}).");
    }
}
