<?php

namespace Infortis\Base\Helper;

use DateTime;

class GetNowBasedOnLocale
{
    protected $locale; //TODO: not needed
    protected $timezone;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
    }
    
    public function getNow()
    {
        return $this->timezone->date()->setTime('0','0','0')->format(
            'Y-M-d H:m:s');        
    }
}
