@extends('layouts.minimal')

@section('content')
    <script type="text/javascript">
        $("document").ready(function() {
            function checkAndSend() {
                if (!('parentIFrame' in window)) {
                    setTimeout(checkAndSend, 16); // setTimeout(func, timeMS)
                } else {
                    window.parentIFrame.sendMessage("logout");
                    return false;
                }
            }
            checkAndSend();
        });
    </script>
@endsection