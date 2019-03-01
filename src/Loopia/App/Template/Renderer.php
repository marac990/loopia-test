<?php

namespace Loopia\App\Template;

interface Renderer
{
    public function render($template, $data = []);
}