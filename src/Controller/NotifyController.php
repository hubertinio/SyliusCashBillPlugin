<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class NotifyController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function index(Request $request): Response
    {
        $output = ['ok'];

        return JsonResponse::fromJsonString(
            json_encode($output)
        );
    }
}
