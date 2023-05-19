<?php

namespace App\Console\Commands;

use App\Service\ReservationService;

use App\Service\LoyalitySystem;
use App\Service\BidService;
use App\Service\PostService;
use Illuminate\Console\Command;
use App\Models\postModel;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use App\Models\CityModel;
use Illuminate\Support\Facades\Validator;
use App\Models\savedContacts;
use App\Models\User;
use Laravel\Passport\Passport;
use App\Http\Controllers\userController;

use Illuminate\Support\Facades\Http;

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

  protected $validator;
  /**
   * Execute the console command.
   *
   * @return int
   */

  protected $LocalPhotoPath = '/PHOTOS';

  public function handle()
  {


    $input = 'carrrot';

    // массив сверяемых слов
    $words  = array(
      'apple', 'pineapple', 'banana', 'orange',
      'radish', 'carrot', 'pea', 'bean', 'potato'
    );




    //$res = PostService::sortTitlesByLevenshtein($input,$words);

    print_r($res);
    die();
    // кратчайшее расстояние пока ещё не найдено
    $shortest = -1;

    // проходим по словам для нахождения самого близкого варианта
    foreach ($words as $word) {

      // вычисляем расстояние между входным словом и текущим
      $lev = levenshtein($input, $word);

      echo($lev."\n");
      // проверяем полное совпадение
      if ($lev == 0) {

        // это ближайшее слово (точное совпадение)
        $closest = $word;
        $shortest = 0;

        // выходим из цикла - мы нашли точное совпадение
        break;
      }

      // если это расстояние меньше следующего наименьшего расстояния
      // ИЛИ если следующее самое короткое слово ещё не было найдено
      if ($lev <= $shortest || $shortest < 0) {
        // устанивливаем ближайшее совпадение и кратчайшее расстояние
        $closest  = $word;
        $shortest = $lev;
      }
    }

    echo "Вы ввели: $input\n";
    if ($shortest == 0) {
      echo "Найдено точное совпадение: $closest\n";
    } else {
      echo "Вы не имели в виду: $closest?\n";
    }
  }

  public function test($contacts = null)
  {
    if (isset($contacts)) {
      printf('Yes');
    } else {
      printf('Error');
    }
  }
  function shellSort($arr)
  {
    $n = count($arr);

    // Выбор шага
    $gap = floor($n / 2);

    // Повторение сортировки
    while ($gap > 0) {
      // Проход по массиву с шагом gap
      for ($i = $gap; $i < $n; $i++) {
        // Сохранение текущего элемента и его индекса
        $temp = $arr[$i];
        $j = $i;
        // Сдвиг элементов на шаг gap
        while ($j >= $gap && $arr[$j - $gap] > $temp) {
          $arr[$j] = $arr[$j - $gap];
          $j -= $gap;
        }
        // Вставка сохраненного элемента на правильное место
        $arr[$j] = $temp;
      }
      // Уменьшение шага на половину
      $gap = floor($gap / 2);
    }
    // Возврат отсортированного массива
    return $arr;
  }
}
