<?php

namespace App\Console\Commands;

use App\Service\PostService;
use Illuminate\Console\Command;
use App\Models\postModel;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BitrixTestCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'bitrix:test';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command description';

  /**
   * Execute the console command.
   *
   * @return int
   */

  protected $httpClient;
  protected $path = '/home/in-good-hands/web/in-good-hands.dev.mind4.me/application/storage/res.csv';
  protected $resultPath = '/home/in-good-hands/web/in-good-hands.dev.mind4.me/application/storage/result.csv';

  protected $hook = 'https://mind4.bitrix24.ru/rest/212/3hzyklya74ayd81h/';
  protected $LocalPhotoPath = '/PHOTOS';
  protected $dealUrl = 'https://slavutich.bitrix24.ru/crm/deal/details/';

  public function handle()
  {

    $this->httpClient = new Client([
      'base_uri' => $this->hook,
      'http_errors' => false,
      'verify' => false,
      'protocols' => ['https'],
      'delay' => 500
    ]);


    $date = Carbon::now();
    // $res = $this->callMethod('im.recent.list', 'get', [
    //   'LAST_MESSAGE_DATE' => '2023-5-12'
    // ]);

    $date = $date->format(Carbon::ATOM);
    
    echo($date);
    die();
    $res = $this->callMethod('im.recent.get', 'get', [
      //'LAST_MESSAGE_DATE' => '2023-5-12'
      //'LAST_UPDATE' => '2022-05-8T10:45:31+02:00'
    ]);


    //echo((json_encode($res->result)));

    foreach ($res->result as $chat) {
      print_r(($chat->title)."\n");
    }








    die();
  }




  public function callMethod($apiMethod = '', $httpMethod = 'get', $params = [])
  {
    if ($httpMethod == 'get') {
      $requestBody = [
        'query' => $params
      ];
    } else {
      $requestBody = [
        'form_params' => $params,
      ];
    }
    try {
      $response = $this->httpClient->request($httpMethod, $apiMethod, $requestBody);
      if ($response->getStatusCode() === 200) {
        $responseData = $response->getBody()->getContents();
        return json_decode($responseData);
      } else {
        return ['success' => false, 'error' => 'Нет связи с срм ' . $apiMethod . '. Ответ - ' . $response->getStatusCode() . '. ' . print_r($response->getBody()->getContents(), true)];
      }
    } catch (GuzzleException $e) {
      die(json_encode(['success' => false, 'error' => $e->getMessage()]));
    }
  }
}
