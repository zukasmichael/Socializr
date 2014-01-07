<?php

namespace Zmqueue;

interface Worker
{
    public function __construct(Request $request, \Silex\Application $app);
    public function __invoke();
    public function isValid(Request $request);
} 