<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DuplicateCheck;
use App\Models\Hotel;
use App\Services\DuplicateCheckService;
use App\Services\MembershipService;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function __construct(
        private DuplicateCheckService $duplicateCheck,
        private MembershipService $membership,
        private OtpService $otp,
    ) {}

    public function create(): View
    {
        return view('auth.register', [
            'hotels' => Hotel::where('is_active', true)->get(),
        ]);
    }

    /** AJAX duplicate check saat customer mengisi form */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:30'],
            'dob' => ['nullable', 'date'],
            'id_number' => ['nullable', 'string', 'max:50'],
        ]);

        $results = $this->duplicateCheck->check($validated);

        if ($results === []) {
            return response()->json(['status' => 'clear']);
        }

        // Simpan catatan duplicate untuk review admin
        $hasExact = false;
        $payload = [];
        foreach ($results as $result) {
            /** @var \App\Models\Member $member */
            $member = $result['member'];
            $hasExact = $hasExact || $result['exact'];

            DuplicateCheck::create([
                'submitted_data' => $validated,
                'matched_member_id' => $member->id,
                'matched_fields' => $result['fields'],
                'status' => $result['exact'] ? 'potential_duplicate' : 'potential_duplicate',
            ]);

            $payload[] = [
                'member_no' => $member->member_no,
                'full_name' => $member->full_name,
                'email' => $member->email,
                'phone' => $member->phone,
                'hotel' => $member->hotel->name,
                'level' => $member->level->name,
                'status' => $member->status,
                'fields' => $result['fields'],
                'exact' => $result['exact'],
            ];
        }

        return response()->json([
            'status' => $hasExact ? 'duplicate' : 'review',
            'matches' => $payload,
        ]);
    }

    /**
     * Update #20/#22 — kirim kode OTP verifikasi email (anti-spambot).
     * AJAX POST { email }
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate(
            ['email' => ['required', 'email', 'max:150']],
            ['email.required' => 'Email wajib diisi.', 'email.email' => 'Format email tidak valid.']
        );

        try {
            $otp = $this->otp->send($validated['email']);

            return response()->json([
                'status' => 'sent',
                'message' => 'Kode OTP telah dikirim ke ' . $validated['email'] . '. Berlaku ' . OtpService::TTL_MINUTES . ' menit.',
                'resend_in' => OtpService::RESEND_SECONDS,
            ]);
        } catch (\DomainException $e) {
            $last = \App\Models\OtpCode::where('email', strtolower(trim($validated['email'])))
                ->where('purpose', 'registration')->latest()->first();

            return response()->json([
                'status' => 'throttled',
                'message' => $e->getMessage(),
                'resend_in' => $last ? max(1, OtpService::RESEND_SECONDS - $last->created_at->diffInSeconds(now())) : 60,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim OTP. Silakan coba beberapa saat lagi.',
            ], 500);
        }
    }

    /** AJAX POST { email, code } — verifikasi kode OTP */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'code' => ['required', 'digits:6'],
        ]);

        try {
            $this->otp->verify($validated['email'], $validated['code']);

            return response()->json(['status' => 'verified']);
        } catch (\DomainException $e) {
            return response()->json(['status' => 'invalid', 'message' => $e->getMessage()]);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages());

        // Update #20 — verifikasi OTP email wajib sebelum registrasi (anti-spambot)
        try {
            $this->otp->verify($validated['email'], (string) $request->input('otp_code'));
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors(['otp_code' => $e->getMessage()]);
        }

        // Final server-side duplicate check (keamanan)
        $results = $this->duplicateCheck->check($validated);

        foreach ($results as $result) {
            if ($result['exact']) {
                DuplicateCheck::create([
                    'submitted_data' => $validated,
                    'matched_member_id' => $result['member']->id,
                    'matched_fields' => $result['fields'],
                    'status' => 'potential_duplicate',
                ]);

                return back()->withInput()
                    ->with('duplicate_blocked', true)
                    ->with('duplicate_member', $result['member']->member_no);
            }
        }

        $member = $this->membership->registerMember($validated);

        return redirect()->route('register.success', $member->member_no);
    }

    public function success(string $memberNo): View
    {
        $member = \App\Models\Member::where('member_no', $memberNo)
            ->with('hotel', 'level', 'payments')
            ->firstOrFail();

        return view('auth.success', ['member' => $member]);
    }

    private function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'phone_country_code' => ['nullable', 'string', 'max:8', 'regex:/^\+[0-9]{1,4}$/'],
            'proof_reference' => ['nullable', 'string', 'max:80'],
            'otp_code' => ['required', 'digits:6'],
            'dob' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female'],
            'address' => ['nullable', 'string', 'max:500'],
            'id_type' => ['nullable', 'in:ktp,passport,sim,other'],
            'id_number' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:150'],
            'occupation' => ['nullable', 'string', 'max:150'],
            'hotel_id' => ['required', 'exists:hotels,id'],
            'membership_type' => ['required', 'in:paid,free'],
        ];
    }

    private function messages(): array
    {
        return [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone_country_code.regex' => 'Kode negara harus berformat +62, +65, dst.',
            'otp_code.required' => 'Kode OTP wajib diisi — klik "Kirim Kode OTP" pada langkah verifikasi email.',
            'otp_code.digits' => 'Kode OTP harus 6 digit angka.',
            'dob.before' => 'Tanggal lahir harus sebelum hari ini.',
            'hotel_id.required' => 'Pilih hotel.',
            'membership_type.required' => 'Pilih tipe membership.',
        ];
    }
}
