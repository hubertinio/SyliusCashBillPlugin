<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Hubertinio\SyliusApaczkaPlugin\Service\PushService;

final class StatusChangeNotificationController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            if ('POST' === $_SERVER['REQUEST_METHOD']) {
                $content = file_get_contents('php://input');
                $this->logger->debug(__METHOD__, [
                    'post' => $content
                ]);
            }

            $content = (string) $request->getContent();
            $this->logger->debug(__METHOD__, [
                'content' => json_encode($content),
                'headers' => json_encode($request->headers),
                'query' => json_encode($request->query),
            ]);

            return JsonResponse::fromJsonString(json_encode('ok'));
        } catch (Throwable $e) {
            $this->logger->critical($e->getMessage());

            return JsonResponse::fromJsonString(
                json_encode(['error' => $e->getMessage()]),
                Response::HTTP_BAD_REQUEST
            );
        }

    }
}
