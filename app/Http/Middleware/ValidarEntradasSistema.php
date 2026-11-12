<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class ValidarEntradasSistema
{
    private const MAX_FILE_BYTES = 8 * 1024 * 1024;
    private const MAX_IMAGE_PIXELS = 8_000_000;

    public function handle(Request $request, Closure $next): Response
    {
        foreach ($request->allFiles() as $field => $files) {
            foreach ($this->flattenFiles($files) as $file) {
                if (!$file instanceof UploadedFile) {
                    continue;
                }
                if (!$file->isValid()) {
                    throw ValidationException::withMessages([$field => 'El archivo no se recibió correctamente.']);
                }
                $isImageField = preg_match('/^(imagen|foto|avatar|logo)(?:\.\d+)*$/i', (string) $field) === 1;
                $isEvidenceField = preg_match('/^(evidencia|adjunto)(?:\.\d+)*$/i', (string) $field) === 1;
                $maxBytes = $isImageField || ($isEvidenceField && str_starts_with((string) $file->getMimeType(), 'image/')) ? 5 * 1024 * 1024 : self::MAX_FILE_BYTES;
                if ($file->getSize() > $maxBytes) {
                    throw ValidationException::withMessages([$field => 'El archivo excede el límite permitido de '.($maxBytes / 1024 / 1024).' MB.']);
                }
                if (!$isImageField && !$isEvidenceField) {
                    throw ValidationException::withMessages([$field => 'Este formulario no admite un archivo en ese campo.']);
                }

                if ($isImageField || ($isEvidenceField && str_starts_with((string) $file->getMimeType(), 'image/'))) {
                    $mime = $file->getMimeType();
                    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                    $dimensions = @getimagesize($file->getPathname());
                    if (!in_array($mime, $allowed, true) || $dimensions === false) {
                        throw ValidationException::withMessages([$field => 'Sube una imagen JPEG, PNG o WebP válida.']);
                    }
                    [$width, $height] = $dimensions;
                    if ($width > 2560 || $height > 2560 || $width * $height > self::MAX_IMAGE_PIXELS) {
                        throw ValidationException::withMessages([$field => 'La imagen debe medir como máximo 2560 × 2560 píxeles.']);
                    }
                } elseif ($isEvidenceField && !in_array($file->getMimeType(), ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'], true)) {
                    throw ValidationException::withMessages([$field => 'Adjunta una imagen, PDF o documento de Word válido.']);
                }
            }
        }

        return $next($request);
    }

    /** @return list<UploadedFile> */
    private function flattenFiles(mixed $files): array
    {
        if ($files instanceof UploadedFile) {
            return [$files];
        }
        if (!is_array($files)) {
            return [];
        }

        $flattened = [];
        foreach ($files as $file) {
            array_push($flattened, ...$this->flattenFiles($file));
        }

        return $flattened;
    }
}
