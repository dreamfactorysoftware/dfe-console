@extends('emails.responsive')
{{--

 This blade is for generating export emails.

 The following view data is expected:

 $headTitle           The title of the email/page
 $contentHeader       The callout/header of the email's body
 $firstName           The first name of the recipient
 $downloadLink        The link to the download. FALSE indicates failure
 $emailBody           The optional guts of the email. Will be placed in its own div

 Provided to all views

 $dashboard_url             The dashboard's URL
 $support_email_address     The support email address

--}}
@section('contentBody')
    <div>
        @if(false !== $downloadLink)
            <p>Your requested export is complete. It may be downloaded it for up to {{ $daysToKeep || 30 }} days, from the following
                link:<br />
                <br />
                <strong><a href="{{ $downloadLink }}" target="_blank">{{ $downloadLink }}</a></strong>
            </p>
        @else
            <p>The export you requested did not complete properly. Please make sure your instance is up and running and that you've logged into the Admin
                application at least one time. If the issue persists, please contact support.</p>
        @endif
    </div>
@stop
