<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
class savePhotoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'photo:save {images} {folder}';

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
        $images = $this->argument('images');
        $folder = $this->argument('folder');
        
        $this->savePhoto($images,$folder);
        return Command::SUCCESS;
    }
    function savePhoto($images, $folder)
    {
        foreach ($images as $key => $data) {
            $path = $folder . '/' . $key . '.jpeg';
            $data = base64_decode($data);
            Storage::disk("local")->put($path, $data);
        }
    }
}
