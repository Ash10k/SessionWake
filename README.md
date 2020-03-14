# SessionWake
File Based User Authentication System using JWT that Supports multiple session and session control from multiple devices.

### The problem
While using an authentication system like oAuth we use JWTs, that come in two forms, i.e., Access & Refresh Tokens. Access Tokens are use for authorize any request without making sure if the refresh token associated with is already disarmed from the server (in case of a logout).
It is an evident fact that if logging out of multiple devices is allowed, there is no traditional way to invalidate the access tokens from client side and reading the database for validating the Access Token is too much resourceful. Even AWS Cognito couldn't completely invalidate it's Access Tokens after a successful logout.

### Objective
1. To employ/enable session control (logging out of other devices) by invalidating the tokens server side while using JWT
2. Eradicate Refreshing of the tokens. Only a single token will be used that doesn't need any refresh even with no database validation.
3. Create unique pathways to server Secrets that need not be rotated.

### Prequisites
1. Understanding of JWT (https://jwt.io/)

### Languages & Respective Libraries Used (Server Side)
1. PHP (Libs: rbdwllr/reallysimplejwt, ramsey/uuid)

### Languages & Respective Libraries Used (Client Side)
1. HTML5
2. CSS3
5. JS (Libs: jQuery, Cookie.js)

### Host File Configuration
your.ip.v4/6.address  api.sessionwake.localhost
