var emailGuyForm = {};
(function(context) {

  var processing = false;

  var settings = {
    "errors": {
      "required": "Fields with '*' are required.",
      "email": "Email must be valid.",
      "server": "There was an error sending. Please try again.",
      "csrf": "Token missing. Please try again."
    }
  }

  var emailFormSelector = "form#emailGuy";

  var emailFormInputSelector = emailFormSelector + " input, " + emailFormSelector + " textarea";

  var emailFormSubmitSelector = emailFormSelector + " .submit";

  var emailFormSubmitLoaderSelector = emailFormSubmitSelector + " .loader";

  var emailFormStatusSelector = emailFormSelector + " .status";

  var emailFormCSRFSelector = emailFormSelector + " .csrf";

  /* *
   * Main function
   */
  context.init = function(){

    context.getCSRF();

    $(document).on("submit", emailFormSelector, function(e){

      e.preventDefault();

      var emailForm = $(this);

      var emailFormStatus = $(emailFormStatusSelector);

      var emailFormRequired = emailForm.find(".required");

      var status = context.validateFields(emailFormRequired);

      if(!status){

        context.sendEmail(emailForm);

      } else {

        emailFormStatus.html(status);

      }


    });

    $(document).on("click", emailFormSubmitSelector, function(e) {

      e.preventDefault();

      $(emailFormSelector).trigger("submit");

    });


    $(document).on("focus", emailFormInputSelector, function(e) {

      e.preventDefault();

      $(emailFormStatusSelector).html("");

    });

  };

  /* *
   * Check required field
   * @param string
   * @param boolean
   * @returns status message
   */
  context.required = function(val, csrf){

    if(val){
      return "";
    } else {

      if(!csrf){
        return settings.errors.required;
      } else {
        return settings.errors.csrf;
      }

    }

  };

  /* *
   * Validate email
   * @param string
   * @returns status message
   */
  context.validateEmail = function(val){

    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(val)){
      return "";
    }

    return settings.errors.email;

  };

  /* *
   * Validates all fields
   * @param array of elements
   * @returns status message
   */
  context.validateFields = function(elms){

    var status = "";

    elms.each(function() {

      var elm = $(this);

      var val = elm.val();

      var csrf = false;

      if(elm.hasClass('csrf')){
        csrf  = true;
      }

      status = context.required(val, csrf);

      if(!status && elm.hasClass('email')){
        status = context.validateEmail(val);
      }



      if(status){

        return false;

      }

    });

    return status;

  };

  /* *
   * Sends email, updates status
   * @param form element
   */
  context.sendEmail = function(emailForm){

    if(!processing){
      processing = true;

      var emailFormLoader = $(emailFormSubmitLoaderSelector)

      emailFormLoader.show();

      $.ajax({

        type: emailForm.attr("method"),
        url: emailForm.attr("action"),
        data: emailForm.serialize()

      }).done(function(data) {

        context.completeSendEmail(data);

      }).fail(function() {

        context.completeSendEmail(settings.error.server);

      });

    }

  };

  /* *
   * Finishes email send, updates status, resets loader
   * @param string
   */
  context.completeSendEmail = function(message){

    var emailFormLoader = $(emailFormSubmitLoaderSelector)

    var emailFormStatus = $(emailFormStatusSelector);

    emailFormStatus.html(message);
    emailFormLoader.hide();
    processing = false;

  };

  /* *
   * Gets CSRF token
   */
  context.getCSRF = function(){

    $.ajax({

      type: "GET",
      url: "../php/getCSRF.php"

    }).done(function(data) {

      $(emailFormCSRFSelector).val(data);

    }).fail(function(data) {

      console.log(settings.errors.server);

    });

  };

})(emailGuyForm);


$(document).ready(function(){

  emailGuyForm.init();

});
