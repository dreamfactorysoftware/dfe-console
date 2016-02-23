== FastTrack
The FastTrack endpoint is intended to provide a one-click entry into a DreamFactory%trade; instance.

Calling this endpoint performs the following operations:

# Validates the request
# A DFE Dashboard user is created with the provided information
# An instance is created named using a portion of the email address
# The instance is initialized and the database is created
# An instance admin is created on the new instance
# The instance admin is logged in and the browser is redirected to the new instance
