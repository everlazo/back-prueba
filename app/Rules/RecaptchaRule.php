<?php

namespace App\Rules;

use App\Services\RecaptchaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RecaptchaRule implements ValidationRule
{
    private string $action;
    private float $minScore;

    /**
     * Constructor de la regla de validación reCAPTCHA
 
     */
    public function __construct(string $action = 'form_submit', float $minScore = 0.5)
    {
        $this->action = $action;
        $this->minScore = $minScore;
    }

    /**
     * Ejecutar la validación reCAPTCHA
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $recaptchaService = app(RecaptchaService::class);
        
        // Si reCAPTCHA no está habilitado, pasar la validación
        if (!$recaptchaService->isEnabled()) {
            return;
        }

        // Validar el token
        if (!$recaptchaService->verify($value, $this->action, $this->minScore)) {
            $fail('La verificación reCAPTCHA ha fallado. Por favor, inténtalo de nuevo.');
        }
    }
}
