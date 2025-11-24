<?php

namespace App\Modules\SecurityManagement\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Modules\SecurityManagement\Models\OtpCode;

class OtpCodeRender extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public OtpCode $otp, public array $options = [])
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('business-core::components.otp-code-render');
    }
}
