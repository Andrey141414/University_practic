<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\postModel;
use Illuminate\Support\Carbon;
use App\Models\postStatus;
use App\Service\PostService;
use Illuminate\Support\Facades\Storage;

class generatePosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post:generate';

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
    public function handle()
    {

        // Storage::disk("local")->makeDirectory('/PHOTOS/16/121',0777);

        // return;
        $props = [
                'title' => 'Empty',
                'description' => 'Empty',
                'id_category' => 1,
                'image_set' => [
                    config('photo.generatePhoto'),
                    config('photo.generatePhoto'),
                ],
                "address" =>  [
                    "title" =>  "г Барнаул, Малый Прудской пер, д 46, кв 34",
                    "longitude" =>  83.7543103,
                    "latitude" =>  53.3285879
                ],
                'id_city' => 1,
            ];
        $props['address']['latitude'] = (float)$props['address']['latitude'];
        $props['address']['longitude'] = (float)$props['address']['longitude'];
        $props['created_at'] = Carbon::now();
        $props['updated_at'] = Carbon::now();
        $props['id_user'] = 16;
        $props['id_status'] = postStatus::getStatusid('active');
        $props['address'] = json_encode($props['address']);
        $id_post = PostService::newPost($props);
        return Command::SUCCESS;
    }
}
