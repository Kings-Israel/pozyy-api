<?php

use App\Http\Controllers\GamesController;
use App\SpotDifference;
use App\Trivia;
use App\TriviaCategory;
use App\TriviaQuestion;
use App\TwoPicsGame;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;

class GamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $triviaCategoryOne = TriviaCategory::create([
            'name' => 'Sample Trivia Category One',
            'imagePath' => 'http://pozzy.test/storage/games/trivia/category/xIVn56UkKE1Dx6q8hPo5qhUq7KHP2GTb3Qaf2Dzg.jpeg'
        ]);

        $triviaCategoryTwo = TriviaCategory::create([
            'name' => 'Sample Trivia Category Two',
            'imagePath' => 'http://pozzy.test/storage/games/trivia/category/wueg2R92pYkXFyZwQ3tUClxd6mg2hmK0LRd1Fwnb.jpeg'
        ]);

        $triviaOne = Trivia::create([
            'trivia_category_id' => $triviaCategoryOne->id,
            'title' => 'Sample Trivia One',
            'description' => 'Sample trivia one description',
            'imagePath' => 'http://pozzy.test/storage/games/trivia/trivia/abWk9ZN6B6AYlG2I6wzMK8JCnEQetzwJCh5y4mpa.jpeg'
        ]);

        $triviaTwo = Trivia::create([
            'trivia_category_id' => $triviaCategoryTwo->id,
            'title' => 'Sample Trivia Two',
            'description' => 'Sample trivia two description',
            'imagePath' => 'http://pozzy.test/storage/games/trivia/trivia/chyaXMGB5u1OdWpdfs4mOrolKnu4ES5KEfVF7TMP.jpeg'
        ]);

        $triviaOneQuestionOne = new Request([
            'trivia_id' => $triviaOne->id,
            'text' => 'Sample question one for trivia one',
            'duration' => 5000,
            'options' => [
                'True', 'False', 'I don\'t Know'
            ],
            'correct' => 'I don\'t Know'
        ]);

        $triviaOneQuestionTwo = new Request([
            'trivia_id' => $triviaOne->id,
            'text' => 'Sample question two for trivia one',
            'duration' => 6000,
            'options' => [
                'Cheetah', 'Giraffe', 'Leopard', 'Elephant'
            ],
            'correct' => 'Leopard'
        ]);

        $triviaOneQuestionThree = new Request([
            'trivia_id' => $triviaOne->id,
            'text' => 'Sample question three for trivia one',
            'duration' => 10000,
            'options' => [
                'Cars', 'Buses', 'Trains', 'Aeroplanes'
            ],
            'correct' => 'Buses'
        ]);

        $triviaTwoQuestionOne = new Request([
            'trivia_id' => $triviaTwo->id,
            'text' => 'Sample question one for trivia two',
            'duration' => 8000,
            'options' => [
                'Facebook', 'Google', 'Apple', 'Amazon', 'Tesla'
            ],
            'correct' => 'Apple'
        ]);

        $triviaTwoQuestionTwo = new Request([
            'trivia_id' => $triviaTwo->id,
            'text' => 'Sample question two for trivia two',
            'duration' => 3000,
            'options' => [
                'Apple', 'Samsung', 'Nokia', 'Huawei', 'One Plus'
            ],
            'correct' => 'Samsung'
        ]);

        $gamesController = new GamesController;

        $gamesController->addTriviaQuestions($triviaOneQuestionOne);
        $gamesController->addTriviaQuestions($triviaOneQuestionTwo);
        $gamesController->addTriviaQuestions($triviaOneQuestionThree);
        $gamesController->addTriviaQuestions($triviaTwoQuestionOne);
        $gamesController->addTriviaQuestions($triviaTwoQuestionTwo);

        TwoPicsGame::create([
            'pictureOne' => 'http://pozzy.test/storage/games/twopics/RZISgROHiv7jHrR2D7YKXGyURaFTqHBbFeiHOOlb.jpeg',
            'pictureTwo' => 'http://pozzy.test/storage/games/twopics/zpkaCVflwgIN8bOpmEytJOkuzhuUA4iCSywYZCIp.jpeg',
            'answer' => 'Lion',
            'hint' => 'Sample Hint for the difference',
            'duration' => 6000
        ]);

        TwoPicsGame::create([
            'pictureOne' => 'http://pozzy.test/storage/games/trivia/trivia/abWk9ZN6B6AYlG2I6wzMK8JCnEQetzwJCh5y4mpa.jpeg',
            'pictureTwo' => 'http://pozzy.test/storage/games/trivia/category/wueg2R92pYkXFyZwQ3tUClxd6mg2hmK0LRd1Fwnb.jpeg',
            'answer' => 'Lion',
            'hint' => 'Sample Hint for the difference',
            'duration' => 6000
        ]);

        TwoPicsGame::create([
            'pictureOne' => 'http://pozzy.test/storage/games/trivia/category/xIVn56UkKE1Dx6q8hPo5qhUq7KHP2GTb3Qaf2Dzg.jpeg',
            'pictureTwo' => 'http://pozzy.test/storage/games/trivia/trivia/abWk9ZN6B6AYlG2I6wzMK8JCnEQetzwJCh5y4mpa.jpeg',
            'answer' => 'Lion',
            'hint' => 'Sample Hint for the difference',
            'duration' => 6000
        ]);

        SpotDifference::create([
            'firstImagePath' => 'http://pozzy.test/storage/games/trivia/trivia/abWk9ZN6B6AYlG2I6wzMK8JCnEQetzwJCh5y4mpa.jpeg',
            'secondImagePath' => 'http://pozzy.test/storage/games/trivia/category/wueg2R92pYkXFyZwQ3tUClxd6mg2hmK0LRd1Fwnb.jpeg',
            'differences' => ['Difference One', 'Difference Two', 'Difference Three', 'Difference Four'],
            'duration' => 6000
        ]);

        SpotDifference::create([
            'firstImagePath' => 'http://pozzy.test/storage/games/twopics/zpkaCVflwgIN8bOpmEytJOkuzhuUA4iCSywYZCIp.jpeg',
            'secondImagePath' => 'http://pozzy.test/storage/games/twopics/RZISgROHiv7jHrR2D7YKXGyURaFTqHBbFeiHOOlb.jpeg',
            'differences' => ['First Difference', 'Second Difference', 'Third Difference', 'Fourth Difference', 'Fifth Difference'],
            'duration' => 5000
        ]);

        SpotDifference::create([
            'firstImagePath' => 'http://pozzy.test/storage/games/spotdifference/5NtLeu1pI4f27rCKIz7SKtH8AqWQeAtQg6ta01se.jpeg',
            'secondImagePath' => 'http://pozzy.test/storage/games/spotdifference/uo2niqcDtQebua2l9nwWDyrI62WU0JsnqnGeTk23.jpeg',
            'differences' => ['First', 'Second', 'Third', 'Fourth', 'Fifth'],
            'duration' => 5000
        ]);
    }
}
