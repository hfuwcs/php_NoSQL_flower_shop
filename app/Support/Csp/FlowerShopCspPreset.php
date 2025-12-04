<?php

namespace App\Support\Csp;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

class FlowerShopCspPreset implements Preset
{
    public function configure(Policy $policy): void
    {
        $policy
            ->add(Directive::BASE, Keyword::SELF)
            ->add(Directive::CONNECT, [Keyword::SELF, 'api.stripe.com'])
            ->add(Directive::DEFAULT, Keyword::SELF)
            ->add(Directive::FORM_ACTION, Keyword::SELF)
            ->add(Directive::IMG, [Keyword::SELF, 'data:', 'via.placeholder.com', 'pos.nvncdn.com', 'https:'])
            ->add(Directive::MEDIA, Keyword::SELF)
            ->add(Directive::OBJECT, Keyword::NONE)
            ->add(Directive::SCRIPT, [Keyword::SELF, Keyword::UNSAFE_EVAL, 'js.stripe.com', 'm.stripe.network'])
            ->add(Directive::STYLE, [Keyword::SELF, 'fonts.bunny.net', Keyword::UNSAFE_INLINE])
            ->add(Directive::FONT, [Keyword::SELF, 'fonts.bunny.net'])
            ->add(Directive::FRAME, [Keyword::SELF, 'js.stripe.com'])
            ->addNonce(Directive::SCRIPT)
            ->addNonce(Directive::STYLE);
    }
}
