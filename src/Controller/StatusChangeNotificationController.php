<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Hubertinio\SyliusApaczkaPlugin\Service\PushService;
use Webmozart\Assert\Assert;
use Hubertinio\SyliusCashBillPlugin\Bridge\CashBillBridgeInterface;

final class StatusChangeNotificationController extends AbstractController
{
    public function __construct(
        private string $debugMode,
        private string $logsDir,
        private Filesystem $filesystem,
        private CashBillBridgeInterface $bridge,
    ) {
    }

    public function index(Request $request): Response
    {
        try {
            $this->debug($request);

            $cmd = $request->query->get('cmd');
            Assert::eq($cmd, 'transactionStatusChanged');

            $cashBillId = (string) $request->query->get('args');
            Assert::notEmpty($cashBillId);

            $cashBillSign = (string) $request->query->get('sign');
            Assert::notEmpty($cashBillSign);

            $this->bridge->handleStatusChange($cashBillId, $cashBillSign);
        } catch (Throwable $e) {
            $this->logger->critical($e->getMessage());
        } finally {
            return new Response('', Response::HTTP_NO_CONTENT);
        }
    }

    public function debug(Request $request): void
    {
        if (!$this->debugMode) {
            return;
        }

        $data = [
            'headers' => $request->headers,
            'query' => $request->query,
        ];

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $input = file_get_contents('php://input');
            $data['body'] = $input;
        }

        $log = $this->logsDir . DIRECTORY_SEPARATOR . 'cashbill.log';

        if ($this->filesystem->exists($log)) {
            $this->filesystem->touch($log);
        }

        error_log(date('Y-m-d H:i:s') . ': ' . PHP_EOL . print_r($data, true), 3, $log);
    }
}
