<?php

declare(strict_types=1);

namespace MasterRO\MailViewer\Controllers;

use eXorus\PhpMimeMailParser\Parser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use MasterRO\MailViewer\Models\MailLog;
use MasterRO\MailViewer\Services\Resource;

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
        $parser = new Parser();
        $parser->setText($mailLog->payload);
        $attachments = $parser->getAttachments();
        foreach ($attachments as $attachment) {
            echo 'Filename : '.$attachment->getFilename().'<br>';            
            echo 'Filetype : '.$attachment->getContentType().'<br>';
            echo 'MIME part string : '.$attachment->getMimePartStr().'<br>';
        }
    }
}
