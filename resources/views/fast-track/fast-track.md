The FastTrack system is intended to provide a one-click entry into a DreamFactory%trade; instance.

Calling this endpoint performs the following operations:

# Validates the request
# A DFE Dashboard user is created with the provided information
# An instance is created named using a portion of the email address
# The instance is initialized and the database is created
# An instance admin is created on the new instance
# The instance admin is logged in and the browser is redirected to the new instance

== How to use FastTrack ==
You must call FastTrack using an HTTP '''POST''' with a '''JSON''' payload.

=== The Endpoint ===
The FastTrack endpoint depends on the domain name of the installation. We'll use <code>dfe.example.com<f/code> as our example installation.

The endpoint to call for this installation is <code>http://console.dfe.example.com/fast-trak</code>.

=== The Payload ===
The payload may contain the following properties:

<source lang="javascript">
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

If the process is successful and <code>redirect</code> is '''true''', the response will be a 302 HTTP redirect directly to the newly launched instance. Otherwise, a standard DFE Console API response will be returned. Responses are detailed in the following sections.

==== Success Response ====

<source lang="javascript">
{
    "success":     true,
    "status_code": 200,
    "response": {
        "user": {
            "id": 123,
            ...
        },
        "instance-id": "joe",
        "instance":    {
            "id": 123
            ...
        },
        "instance-initialized": true,
        "instance-admin":       true,
        "redirect":             {
            "location":           "redirect-uri",
            "status-code":        302,
            "payload": {
                "fastTrackGuid":    "new user's registration request GUID"
            }
        }
    },
    "request": {
        "elapsed":     37.3943,
        "id":          "102fb7bff406b58eca8f8499f461f64204f62ba1",
        "request-uri": "/fast-track",
        "signature":   "oofbIbnrWJq04/+4/K/q9eyDzB71UxZ19huyaDVfbMA=",
        "start":       "2016-02-25T16:32:08-05:00",
        "verb":        "POST"
        "version":     "2.0"
    }
}
</source>

===== Partial Success Response =====

<source lang="javascript">
{
    "success":     true,
    "status_code": 200,
    "response": {
        "user": false|{
            "id": 123,
            ...
        },
        "instance-id": false|"joe",
        "instance":    false|{
            "id": 123
            ...
        },
        "instance-initialized": false|true,
        "instance-admin":       false|true,
        "redirect":             false|{
            "location":           "redirect-uri",
            "status-code":        302,
            "payload": {
                "fastTrackGuid":    "new user's registration request GUID"
            }
        }
    },
    "request": {
        "elapsed":     37.3943,
        "id":          "102fb7bff406b58eca8f8499f461f64204f62ba1",
        "request-uri": "/fast-track",
        "signature":   "oofbIbnrWJq04/+4/K/q9eyDzB71UxZ19huyaDVfbMA=",
        "start":       "2016-02-25T16:32:08-05:00",
        "verb":        "POST"
        "version":     "2.0"
    }
}
</source>

==== Error Response ====

<source lang="javascript">
{
    "success":     false,
    "status_code": 400|404|500,
    "error": {
        "code":    400|404|500,
        "message": "I am an error message"
    },
    "response": null,
    "request": {
        "elapsed":     0.1109,
        "id":          "664d18cd422c5968f603f8fe8f928524220b5855",
        "request-uri": "/fast-track",
        "signature":   "noERPor0Lg76mXDWJ1gsmisueBL5psl6fszpsuqE98A=",
        "start":       "2016-02-25T16:21:25-05:00",
        "verb":        "POST"
        "version":     "2.0"
    }
}
</source>
