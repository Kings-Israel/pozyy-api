<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\{Book};
use Auth;
use Illuminate\Support\Str;

class bookcontroller extends Controller
{
    public function all_books() {
        $books = Book::get();
        return response()->json($books);
    }
    public function add_book(Request $request) {
        $this->validate($request, [
            'book_name' => 'bail|required',
            'isn_no' => 'bail|required|unique:books',
            'cover_image' => 'required',
            'author' => 'required',
            'price' => 'required',
            'c_price' => 'required',
            'grade' => 'required'
        ]);

        $user = Auth::user();

        $exploded = explode(',', $request->cover_image);
        $decoded = base64_decode($exploded[1]);
        if(Str::contains($exploded[0], 'jpeg'))
            $extension = 'jpg';
        else
            $extension = 'png';
        $fileName = time().'.'.$extension;
        $path = public_path('books/').'/'.$fileName;
        file_put_contents($path, $decoded);

        $book = new Book;
        $book->book_name = $request->book_name;
        $book->isn_no = $request->isn_no;
        $book->author = $request->author;
        $book->price = $request->price;
        $book->c_price = $request->c_price;
        $book->grade = $request->grade;
        $book->description = $request->description;
        $book->user_id = $user->id;
        $book->cover_image = $fileName;
        $book->save();

        return response()->json(['Book added', 200]);
    }
    public function edit_book(Request $request, $isn_no) {
        $this->validate($request, [
            'book_name' => 'bail|required',
            'author' => 'required',
            'price' => 'required',
            'c_price' => 'required',
            'grade' => 'required'
        ]);

        $edit = Book::where('isn_no', $isn_no)->first();

        $image = $edit->cover_image;

        if($request->cover_image != $image) {
            $exploded = explode(',', $request->cover_image);
            $decoded = base64_decode($exploded[1]);
            if(Str::contains($exploded[0], 'jpeg'))
                $extension = 'jpg';
            else
                $extension = 'png';
            $fileName = time().'.'.$extension;
            $path = public_path('books/').'/'.$fileName;
            file_put_contents($path, $decoded);
        } else {
            $fileName = $image;
        }

        $edit->update([
            'book_name' => $request->book_name,
            'isn_no' => $isn_no,
            'author' => $request->author,
            'price' => $request->price,
            'c_price' => $request->c_price,
            'grade' => $request->grade,
            'description' => $request->description,
            'cover_image' => $fileName
        ]);
        return response()->json(['Book editted', 200]);
    }
    public function delete_book($id) {
        $book = Book::find($id)->delete();
        return response()->json('Book deleted');
    }
    public function suspend_book($id) {
        $book = Book::where('id', $id)->update([
            'suspend' => true
        ]);
        return response()->json('Book suspended');
    }
    public function unsuspend_book($id) {
        $book = Book::where('id', $id)->update([
            'suspend' => false
        ]);
        return response()->json('Book suspended');
    }
    public function total_books() {
        $book = Book::where('suspend', false)->get()->count();
        return response()->json($book);
    }
}
