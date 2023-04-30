<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SavePhoto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $images;
    protected $folder;
    public function __construct($images, $folder)
    {
        //
        $this->images = $images;
        $this->folder = $folder;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        foreach ($this->images as $key => $data) {
            $path = $this->folder . '/' . $key . '.jpeg';
            Log::debug($path);
            $data = base64_decode($data);
            $img = Image::make($data)->resize(1024, 1024);
            Storage::disk("local")->put($path, $img->encode('jpeg'),'public');
        }
    }
}
