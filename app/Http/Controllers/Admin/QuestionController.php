<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    public function questions()
    {
        $respo = Admin::select('role')->where('id', Auth::guard('admin')->user()->id)->value('role');
        $questions = Question::all();
        return view('admin.faq.faq')->with(compact('questions', 'respo'));
    }

    public function addEditQuestion(Request $request, $id = null)
    {
        if ($id == "") {
            $title = 'Ajouter un faq';
            // ajout de fonctionnalités
            $question = new Question();
            $questiondata = array();

            $message = "Faq a été ajouté avec succès !";
        } else {
            $title = "Modifier le faq";
            $questiondata = Question::where('id', $id)->first();
            $questiondata = json_decode(json_encode($questiondata), true);

            $question = Question::find($id);
            $message = "Faq à été Modifé avec succès !";
        }

        if ($request->isMethod('post')) {
            $data = $request->all();
            $rules = [
                'question' => 'required',
                'response' => 'required',
            ];

            $customMessage = [
                'question.required' => 'La question est requise',
                'response.required' => 'La reponse de la question est requise',
            ];
            $this->validate($request, $rules, $customMessage);

            $question->question = $data['question'];
            $question->response = $data['response'];
            $question->save();
            $request->session()->flash('success_message', $message);
            return redirect('/questions');
        }
        return view('admin.faq.add_edit_faq')->with(compact('title', 'questiondata'));
    }
}
