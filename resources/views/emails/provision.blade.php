@extends('emails.responsive')
{{--

 This blade is for generating provisioning emails.

 The following view data is expected:

 $headTitle           The title of the email/page
 $contentHeader       The callout/header of the email's body
 $firstName           The first name of the recipient
 $instanceName        The name of the instance
 $instanceUrl         The instance's URL
 $emailBody           The optional guts of the email. Will be placed in its own div

 Provided to all views

 $dashboard_url             The dashboard's URL
 $support_email_address     The support email address

--}}
@section('contentBody')
    <div>
        @if(false !== $instanceUrl)
            <p>Congratulations, your DreamFactory&trade; instance has been created!</p>
            <p>Use the following link to log directly into your DreamFactory&trade; instance:</p>
            <p><a href="{{ $instanceUrl }}" target="_blank">{{ $instanceUrl }}</a></p>
        @else
            <p>The requested provisioning of your instance <strong>{{$instanceName}}</strong> did not complete successfully.</p>
        @endif
    </div>
@stop
