<?php

namespace App\Console\Commands;

use App\Service\PostService;
use Illuminate\Console\Command;
use App\Models\postModel;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

use function Amp\Iterator\filter;

class TestCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'test:test';

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
  
   protected $LocalPhotoPath = '/PHOTOS';

  public function handle()
  {


    $posts = postModel::query()->get();
    // print_r($posts[0]);
    // return;
    $posts = json_decode($posts,true);

$post  = $posts[1];

$post = (object) $post;
    //$post  = json_decode($post,true);
    // print_r($post);

    //$post = collect($post);

    print_r( $post);
    return;
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

}
