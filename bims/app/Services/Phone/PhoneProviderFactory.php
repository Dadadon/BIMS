<?php

namespace App\Services\Phone;

use App\Models\Phone\PhoneIntegration;

class PhoneProviderFactory
{
    public static function make(PhoneIntegration $integration): PhoneProviderContract
    {
        return match($integration->type) {
            'freepbx'    => new FreePbxProvider($integration),
            'vicidial'   => new VicidialProvider($integration),
            'custom_sip' => new CustomSipProvider($integration),
            default      => throw new \InvalidArgumentException("Unknown phone provider: {$integration->type}"),
        };
    }
}
