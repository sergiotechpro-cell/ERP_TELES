<?php
namespace App\Services;

use Twilio\Rest\Client as TwilioClient;
use App\Models\MessageLog;

class WhatsAppService {
  protected TwilioClient $twilio;
  protected string $from;

  public function __construct() {
    $sid = env('WHATSAPP_SID');
    $token = env('WHATSAPP_TOKEN');
    $this->from = env('WHATSAPP_FROM', 'whatsapp:+14155238886');
    $this->twilio = new TwilioClient($sid,$token);
  }

  public function send(string $to, string $body, ?int $orderId = null): MessageLog {
    $msg = $this->twilio->messages->create("whatsapp:$to", [
      'from' => $this->from,
      'body' => $body,
    ]);
    return MessageLog::create([
      'order_id'=>$orderId,
      'to'=>"whatsapp:$to",
      'provider'=>'twilio',
      'body'=>$body,
      'status'=>$msg->status ?? null,
      'meta'=>['sid'=>$msg->sid ?? null]
    ]);
  }
}
