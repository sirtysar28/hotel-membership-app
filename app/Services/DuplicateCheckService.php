<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Support\Collection;

class DuplicateCheckService
{
    /**
     * Cek duplikasi member berdasarkan email, phone, ID number, nama+dob.
     * Match dihitung dengan similarity untuk menangkap typo (false positive -> review admin).
     */
    public function check(array $data): array
    {
        $candidates = Member::query()
            ->with('hotel', 'level')
            ->where(function ($q) use ($data) {
                $q->where('email', 'like', trim((string) $data['email']))
                    ->orWhere('phone', 'like', '%' . trim((string) $data['phone']) . '%')
                    ->when(! empty($data['id_number']), fn ($q2) => $q2->orWhere('id_number', trim((string) $data['id_number'])))
                    ->when(! empty($data['dob']), fn ($q2) => $q2->orWhere(fn ($q3) => $q3
                        ->where('full_name', 'like', '%' . trim((string) $data['full_name']) . '%')
                        ->whereDate('dob', $data['dob'])));
            })
            ->get();

        $results = [];
        foreach ($candidates as $member) {
            $fields = $this->matchFields($member, $data);
            if (in_array('match', $fields) || in_array('similar', $fields)) {
                $results[] = [
                    'member' => $member,
                    'fields' => $fields,
                    'exact' => count(array_filter($fields, fn ($v) => $v === 'match')) >= 2,
                ];
            }
        }

        return $results;
    }

    private function matchFields(Member $member, array $data): array
    {
        $fields = [];

        $fields['name'] = $this->compareText($member->full_name, $data['full_name'] ?? '');

        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $fields['email'] = strtolower($member->email) === $email ? 'match' : 'different';

        $phone = preg_replace('/\D/', '', (string) ($data['phone'] ?? ''));
        $memberPhone = preg_replace('/\D/', '', $member->phone ?? '');
        $fields['phone'] = ($phone && $memberPhone && (str_contains($memberPhone, $phone) || str_contains($phone, $memberPhone)))
            ? 'match' : 'different';

        if (! empty($data['id_number'])) {
            $fields['id_number'] = strcasecmp(trim((string) $member->id_number), trim((string) $data['id_number'])) === 0
                ? 'match'
                : $this->compareText((string) $member->id_number, (string) $data['id_number']);
        }

        if (! empty($data['dob'])) {
            $fields['dob'] = $member->dob?->format('Y-m-d') === $data['dob'] ? 'match' : 'different';
        }

        return $fields;
    }

    private function compareText(string $a, string $b): string
    {
        $a = strtolower(trim($a));
        $b = strtolower(trim($b));

        if ($a === '' || $b === '') {
            return 'different';
        }
        if ($a === $b) {
            return 'match';
        }

        similar_text($a, $b, $percent);

        return $percent >= 85 ? 'similar' : 'different';
    }
}
