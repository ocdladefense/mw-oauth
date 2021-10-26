<?php

$GLOBALS["oauth_config"] = array(
    "name"    => "ocdla-sandbox",
    "default" => true,
    "sandbox" => true, // Might be used to determine domain for urls
    "client_id" => "3MVG9cb9UNjJbdEztW.V9X7_BWf9iiymI2B6fSKaNUyXhgIk7zgWaezISngbSNG5X50zGQr826fzw6CjACg.M",
    "client_secret" => "0A31FD57FB092F2D7AAA9D32D692BC4C1D1617B1850ED621227B705874655F78",
    "auth" => array(
        "saml" => array(),
        "oauth" => array(
            "usernamepassword" => array(
                "token_url" => "https://test.salesforce.com/services/oauth2/token",
                "username" => "membernation@ocdla.com.ocdpartial",
                "password" => "asdi49ir4",
                "security_token" => "4te6Z194Uw4SHVHYut71NJvV"
            ),
            "webserver" => array(
                "token_url" => "https://ocdpartial-ocdla.cs217.force.com/services/oauth2/token",
                "auth_url" => "https://ocdpartial-ocdla.cs217.force.com/services/oauth2/authorize",
                "redirect_url" => "http://localhost/mwiki/index.php?title=Special:UserLogin",
                "callback_url" => "https://localhost/jobs"
            )
        )
    )
);
