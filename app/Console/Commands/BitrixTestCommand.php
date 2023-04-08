<?php

namespace App\Console\Commands;

use App\Service\PostService;
use Illuminate\Console\Command;
use App\Models\postModel;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

use function Amp\Iterator\filter;

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

  protected $hook = 'https://slavutich.bitrix24.ru/rest/26456/7of4h196wchufj5j/';
  protected $LocalPhotoPath = '/PHOTOS';
  protected $dealUrl = 'https://slavutich.bitrix24.ru/crm/deal/details/';

  public function handle()
  {

    $resultArr = [];
    $this->httpClient = new Client([
      'base_uri' => $this->hook,
      'http_errors' => false,
      'verify' => false,
      'protocols' => ['https'],
      'delay' => 500
    ]);

    $resIds = [];
    $row = 1;
    if (($handle = fopen($this->path, "r")) !== FALSE) {
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        //echo "<p> $num полей в строке $row: <br /></p>\n";
        $row++;
        for ($c = 0; $c < $num; $c++) {
          $resIds[] =  $data[$c];
        }
      }
      fclose($handle);
    }

    //echo(json_encode($resIds));


    // $i = 0;
    // if ($i++ == 5) {
    //   break;
    // }

    foreach ($resIds as $genericNo) {

      $res = $this->callMethod('crm.deal.list', 'get', [
        'select' => ['ID'],
        'filter' =>
        [
          'UF_CRM_1617011406' => $genericNo,
        ]
      ]);



      $deals = $res->result;
      if (count($deals) == 0) {
        $resultArr[$genericNo][] = null;
        $fp = fopen($this->resultPath, 'a');
        fputcsv($fp, [$genericNo, "", ""]);
        fclose($fp);
      }

      if(count($deals)>1)
      {
        echo("У брони $genericNo больше одной сделки\n");
      }
      foreach ($deals as $deal) {
        $dealData = $this->callMethod('crm.deal.get', 'get', ['ID' => $deal->ID]);
        $resultArr[$genericNo][] = [
          'dealID' => $this->dealUrl . $deal->ID . '/',
          'integration' =>  $dealData->result->UF_CRM_1677056958
        ];

        $integration = 'Да';
        if ($dealData->result->UF_CRM_1677056958 == 0) {
          $integration = 'Нет';
        }
        $fp = fopen($this->resultPath, 'a');
        fputcsv($fp, [$genericNo, $this->dealUrl . $deal->ID . '/', $integration]);
        fclose($fp);
      }
      
    }



    //print_r($resultArr);
    //echo (json_encode($res->result) . "\n\n");
    //$res = $this->callMethod('crm.deal.get', 'get', ['ID' => 354046]);
    // $res = $this->callMethod('crm.deal.list', 'get',[
    //   'filter' =>
    //   [
    //     'UF_CRM_1617011406' => 566912,
    //   ]
    // ]);

    //echo (json_encode($res));



    die();










    $res = PostService::sortPostsByDistance(postModel::all(), 53.35777624982061, 83.73300723555694);

    print_r($res);
    die();
    //53.35190621390563, 83.76646001814443

    print_r($this->getDistanceBetweenPointsNew(
      83.76646001814443,
      53.35190621390563,
      83.79300983896833,
      53.3538540472901
    ));
    return;

    $path = $this->LocalPhotoPath . '/' . 8 . '/' . 66;
    $images_path = Storage::disk("local")->files($path);

    print_r($images_path);
    die();
    $json = '{"\latitude":"53.326089","title":"\u0433 \u0411\u0430\u0440\u043d\u0430\u0443\u043b, \u0443\u043b \u0410\u043d\u0430\u0442\u043e\u043b\u0438\u044f, \u0434 224","longitude":"83.759681"}';
    $json = '"{\"Code\":\"test\",\"Amount\":\"2200\",\"CurrencyCode\":\"RUB\"}"';
    $json = json_decode($json, true)['Code'];
    echo (($json));
    return Command::SUCCESS;
  }


  function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'kilometers')
  {
    $theta = $longitude1 - $longitude2;
    $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
    $distance = acos($distance);
    $distance = rad2deg($distance);
    $distance = $distance * 60 * 1.1515;
    switch ($unit) {
      case 'miles':
        break;
      case 'kilometers':
        $distance = $distance * 1.609344;
    }
    return (round($distance, 2));
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
