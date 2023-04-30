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


    $loyality = new LoyalitySystem(User::find(16));
    
    $loyality->addGoals(3);

    return ;
    $result = ReservationService::changeStatus(8,'completed');
    echo($result);
    return;
    // Пример использования функции
    $arr = array(5, 2, 9, 1, 5, 6);
    echo "Исходный массив: ";
    print_r($arr);
    echo "Отсортированный массив: ";
    print_r($this->shellSort($arr));
    return;

    if (savedContacts::isContactsSaved(16, 256)) {
      printf('Yes');
    } else {
      printf('Error');
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
