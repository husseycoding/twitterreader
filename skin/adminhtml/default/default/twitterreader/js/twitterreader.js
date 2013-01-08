var twitterverify = Class.create({
    afterInit: function() {
        $("row_twitterreader_configuration_callback_url").down("td.value").insert({ top: "<span id=\"twitterreader_apistatus\"></span>" });
        this.showStatus()
    },
    showStatus: function() {
        var el = $("twitterreader_apistatus");
        if (this.status == "consumer_missing") {
            el.update("Consumer Key/Secret missing");
            $("row_twitterreader_configuration_request_token").down("td.value").insert({ top: "<span>Invalid without consumer data</span>" });
            $("row_twitterreader_configuration_access_token").down("td.value").insert({ top: "<span>Invalid without consumer data</span>" });
        } else if (this.status == "verify_credentials") {
            el.update("Verify your credentials <a href=\"" + this.callbackurl + "\">here</a><br />If the page has expired reset the request <a id=\"twitterreader_readyreset\" href=\"javascript:void(null)\">here</a>");
            this.resetListener();
            $("row_twitterreader_configuration_request_token").down("td.value").insert({ top: "<span>Obtained</span>" });
            $("row_twitterreader_configuration_access_token").down("td.value").insert({ top: "<span>Required</span>" });
        } else if (this.status == "bad_callback") {
            el.update("Please check your consumer credentials and make sure you have created an access token, and added a dummy 'Callback URL' to your <a href=\"https://dev.twitter.com/apps\" target=\"_blank\">application</a><br />Reset your credentials <a id=\"twitterreader_readyreset\" href=\"javascript:void(null)\">here</a>");
            this.resetListener();
            $("row_twitterreader_configuration_request_token").down("td.value").insert({ top: "<span>Obtained</span>" });
            $("row_twitterreader_configuration_access_token").down("td.value").insert({ top: "<span>Required</span>" });
        } else if (this.status == "ready") {
            el.update("Verified and ready<br />Reset your credentials <a id=\"twitterreader_readyreset\" href=\"javascript:void(null)\">here</a>")
            this.resetListener();
            $("row_twitterreader_configuration_request_token").down("td.value").insert({ top: "<span>Not required</span>" });
            $("row_twitterreader_configuration_access_token").down("td.value").insert({ top: "<span>Obtained</span>" });
        } else if (this.status == "bad_verify") {
            el.update("Failed to verify your credentials<br />You may need to reset them <a id=\"twitterreader_readyreset\" href=\"javascript:void(null)\">here</a>")
            $("row_twitterreader_configuration_request_token").down("td.value").insert({ top: "<span>Not required</span>" });
            $("row_twitterreader_configuration_access_token").down("td.value").insert({ top: "<span>Possibly invalidated</span>" });
            this.resetListener();
        } else if (this.status == "problem") {
            el.update("There is a problem with your credentials<br />You may need to reset them <a id=\"twitterreader_readyreset\" href=\"javascript:void(null)\">here</a>")
            this.resetListener();
            $("row_twitterreader_configuration_request_token").down("td.value").insert({ top: "<span>Not required</span>" });
            $("row_twitterreader_configuration_access_token").down("td.value").insert({ top: "<span>Invalidated</span>" });
        } else if (this.status == "exceeded") {
            el.update("Maximum requests exceeded<br />This will reset at " + this.reset);
            $("row_twitterreader_configuration_request_token").down("td.value").insert({ top: "<span>Waiting</span>" });
            $("row_twitterreader_configuration_access_token").down("td.value").insert({ top: "<span>Waiting</span>" });
        }
    },
    resetListener: function() {
        $("twitterreader_readyreset").observe("click", function(e) {
            if (confirm("Are you sure?")) {
                window.location = this.reseturl;
            }
        }.bind(this));
    }
});

document.observe("dom:loaded", function() {
    if (typeof(thistwitterverify) == "object") {
        thistwitterverify.afterInit();
    }
});