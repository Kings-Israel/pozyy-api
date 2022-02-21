<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\GeneratedQuestionsController;
// use Illuminate\Support\Facades\Log;

class GenerateQuestions extends Command
{
    protected $signature = 'generate:questions';
    protected $description = 'Generate Questions';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $call = new GeneratedQuestionsController;
        $call->generated_questions();
    }
}
