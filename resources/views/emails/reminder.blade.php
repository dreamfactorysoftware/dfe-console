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
<?php
if (!isset($dashboard_url) || empty($dashboard_url)) {
    $dashboard_url = config('dfe.dashboard-url');
}
?>
@section('contentBody')
    <div>
        @if(false !== $instanceUrl)
            <p>Thank you again for your interest in DreamFactory. Your trial period is scheduled to end in {{$daysRemaining}} days on {{$expDate}}. </p>
            <p>After this date, your DreamFactory&trade; instance <strong>{{ $instanceName }}</strong> will no longer be available.</p>
            <p>If you are interested in setting up your own DreamFactory instance or for questions regarding how to set up and export existing packages into your own instance, please contact
            us at <a href="mailto:dspsales@dreamfactory.com" >dspsales@dreamfactory.com</a> or call +1-650-641-1800.</p>
            <p>To log in to your DreamFactory instance, go to <a href="{{ $instanceUrl }}" target="_blank">{{ $instanceUrl }}</a>.</p>

        @endif
    </div>
@stop
