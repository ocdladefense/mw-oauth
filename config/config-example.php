<?php

$oauth_config = array(
    "name"    => "",
    "default" => true,
    "sandbox" => true, // Might be used to determine domain for urls
    "client_id" => "",
    "client_secret" => "",
    "auth" => array(
        "saml" => array(),
        "oauth" => array(
            "usernamepassword" => array(
                "token_url" => "https://test.salesforce.com/services/oauth2/token",
                "username" => "",
                "password" => "",
                "security_token" => ""
            ),
            "webserver" => array(
                "token_url" => "https://ocdpartial-ocdla.cs217.force.com/services/oauth2/token",
                "auth_url" => "https://ocdpartial-ocdla.cs217.force.com/services/oauth2/authorize",
                "redirect_url" => "https://localhost/oauth/webserver",
                "callback_url" => "https://localhost/jobs"
            )
        )
    )
);
