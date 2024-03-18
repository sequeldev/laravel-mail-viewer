<?php

declare(strict_types=1);

namespace MasterRO\MailViewer\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use MasterRO\MailViewer\Models\MailLog;
use MasterRO\MailViewer\Services\Resource;
use ZBateson\MailMimeParser\Header\HeaderConsts;
use ZBateson\MailMimeParser\MailMimeParser;

class MailController extends Controller
{
    public function __construct(protected Resource $mails)
    {
    }

    public function index(): View
    {
        return view('mail-viewer::mails.index');
    }

    public function emails(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->mails->fetch($request),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->mails->stats(),
        ]);
    }

    public function payload(MailLog $mailLog): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $mailLog->payload,
        ]);
    }

    public function download(MailLog $mailLog, $filename)
    {
        $mailParser = new MailMimeParser();
        $message = $mailParser->parse($mailLog->payload, false);

        $attachments = $message->getAllAttachmentParts();

        foreach($attachments AS $attachment) {
            if ($attachment->getFilename() !== $filename) {
                continue;
            }

            return response()->streamDownload(function() use ($attachment) { echo $attachment->getContent(); }, $attachment->getFilename());
        }

        abort(404);
    }
}
