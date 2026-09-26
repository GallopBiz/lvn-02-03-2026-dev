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

            $clientName = $file->getClientOriginalName();
            if (str_contains($clientName, "\0") || preg_match('/\.(php|phtml|php3|php4|php5|phps|phar|js|html|htm|exe|sh|bat|cmd|svg|asp|aspx)\./i', $clientName)) {
                $validator->errors()->add('tc_file', 'The uploaded file has an invalid filename or prohibited extension.');
                return;
            }

            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension !== 'pdf') {
                $validator->errors()->add('tc_file', 'The uploaded file extension must strictly be .pdf');
                return;
            }

            $handle = @fopen($file->getRealPath(), 'rb');
            $signature = $handle ? fread($handle, 5) : false;
            if ($handle) fclose($handle);
            if ($signature !== '%PDF-') {
                $validator->errors()->add('tc_file', 'The uploaded file content is not a valid PDF document.');
            }
        });
    }
}
