@extends('emails.responsive')
{{--

 This blade is for generating deprovisioning emails.

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
        <p>Your instance <strong>{{ $instanceName }}</strong> has been retired, as requested.</p>
    </div>
@stop
