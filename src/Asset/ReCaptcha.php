<?php

namespace Bolt\Extension\Bolt\BoltForms\Asset;

use Bolt\Asset\File\JavaScript;

class ReCaptcha extends JavaScript
{
    /** @var string */
    protected $htmlLang;

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $theme =  sprintf('<script>var RecaptchaOptions = { theme : "" };</script>', 'clean');
        $api =  sprintf('<script src="https://www.google.com/recaptcha/api.js?hl=%s" async defer></script>', $this->getHtmlLang());
        
        return $theme . $api;
    }

    /**
     * @return string
     */
    public function getHtmlLang()
    {
        return $this->htmlLang;
    }

    /**
     * @param string $htmlLang
     *
     * @return ReCaptcha
     */
    public function setHtmlLang($htmlLang)
    {
        $this->htmlLang = $htmlLang;

        return $this;
    }
}
