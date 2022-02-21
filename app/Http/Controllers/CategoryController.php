<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function create(Request $request)
    {
        $rules = [
            'category_name' => 'required'
        ];

        $message = [
            'required' => 'Please enter the category name to add'
        ];

        $validator = Validator::make($request->all(), $rules, $message);

        if($validator->fails()){
            return response()->json(['messages' => $validator->messages()], 400);
        }

        if ($category = Category::create($request->all())) {
            return response()->json(['category' => $category, 'message' => 'New Category Added']);
        }

        return response()->json(['message' => 'Error'], 401);
    }

    public function delete($id)
    {
        if (Category::destroy($id)) {
            return response()->json(['message' => 'Category Deleted']);
        } else {
            return response()->json(['message' => 'Error'], 401);
        }
    }
}
