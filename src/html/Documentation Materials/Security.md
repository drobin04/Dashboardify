# About 

# Login

Hashing of passwords is performed client side ***AND*** server side. 

This ensures that passwords do not leave the client device in plaintext, nor could they be exposed from the database in a meaningful way that could enable passing them to authenticate and impersonate a user. 

This also adds a layer of complexity to reverse engineering the password.

## Limiting Failed Logins

WIP - Limit Failed Logins Unless Cookie For Prior Successful Auth Exists

Cookie Name - successful_auth_for_id

