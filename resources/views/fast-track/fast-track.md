The FastTrack system is intended to provide a one-click entry into a DreamFactory%trade; instance.

Calling this endpoint performs the following operations:

# Validates the request
# A DFE Dashboard user is created with the provided information
# An instance is created named using a portion of the email address
# The instance is initialized and the database is created
# An instance admin is created on the new instance
# The instance admin is logged in and the browser is redirected to the new instance

== How to use FastTrack ==
You must call FastTrack with an HTTP '''POST''' with a '''JSON''' payload.

=== The Endpoint ===
The FastTrack endpoint depends on the domain name of the installation. We'll use <code>dfe.example.com<f/code> as our example installation.

The endpoint to call for this installation is <code>http://console.dfe.example.com/fast-trak</code>.

=== The Payload ===
The payload may contain the following properties:

<source>
{
  "redirect":   true|false,
  "email":      "joe@blow.com",
  "first-name": "Joe",
  "last-name":  "Blow",
  "password":   "gratePassW0rd!",
  "nickname":   "Joe",
  "company":    "",
  "phone":      ""
}
</source>

All fields are required except <code>redirect</code>, <code>company</code>, <code>nickname</code> and <code>phone</code>.

If the process is successful and <code>redirect</code> is '''true''', the response will be a 302 HTTP redirect directly to the newly launched instance. Otherwise, a standard DFE Console API response will be returned. The response details are as follows:

<source>
{
  "user": false|{
    "id": 123,
    ...
  },
  "instance-id": "joe@blow.com",
  "instance":    false|{
    "id": 123
    ...
  },
  "instance-initialized": true|false,
  "instance-admin":       true|false,
  "redirect":             false|{
    "location":           "redirect-uri",
    "status-code":        302,
    "payload": {
      "fastTrackGuid":    "new user's registration request GUID"
    }
}
</source>
