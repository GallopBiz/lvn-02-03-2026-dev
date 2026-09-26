<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentTcFileRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'scholar_no' => ['required', 'string', 'max:50'],
            'tc_file' => ['required', 'file', 'max:500', 'mimes:pdf', 'mimetypes:application/pdf,application/x-pdf'],
        ];
    }
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $file = $this->file('tc_file');
            if (!$file || !$file->isValid()) return;
            $handle = @fopen($file->getRealPath(), 'rb'); $signature = $handle ? fread($handle, 5) : false;
            if ($handle) fclose($handle);
            if ($signature !== '%PDF-') $validator->errors()->add('tc_file', 'The uploaded file must be a valid PDF.');
        });
    }
}
