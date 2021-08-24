<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <title></title>
    </head>
    <body>
        <h1> Requests </h1>
        <table class="table table-bordered">
            <thead>
            <tr>
                <td><b>Subject</b></td>
                <td><b>Description</b></td>
                <td><b>Status</b></td>
                <td><b>Created By</b></td>
                <td><b>Date</b></td>
            </tr>
            </thead>
            <tbody>
            @foreach($requests as $request)
                <tr>
                    <td>
                        {{$request->subject}}
                    </td>
                    <td>
                        {{$request->description}}
                    </td>
                    <td>
                        {{$request->status->name}}
                    </td>
                    <td>
                        {{$request->user->name}} ({{$request->user->email}})
                    </td>
                    <td>
                        {{$request->updated_at}}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </body>
</html>
