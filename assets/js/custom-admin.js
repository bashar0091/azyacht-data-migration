jQuery(document).ready(function ($) {
  let isAjaxRunning = false;

  function delete_data_ajax(selector = "", action = "", posttype = "") {
    $(document).on("submit", selector, function (e) {
      e.preventDefault();
      var t = $(this);

      if (
        confirm(
          "❗ ⚠️Are you sure you want to delete this? All data will be lost and cannot be restored."
        )
      )
        if (confirm("⚠️ Please backup first, If you delete data by mistake")) {
          t.find(".reloading_text").html(
            `<span style="color:red;">Delete in Progress... This will take time, Don't Close this window or browser</span>`
          );
          t.find("button").text("Deleting...");
          isAjaxRunning = true;
          $.ajax({
            url: dataAjax.ajaxurl,
            type: "POST",
            data: {
              action: action,
              posttype: posttype,
            },
            success: function (response) {
              if (response.success) {
                t.find(".reloading_text").html(
                  `<span style="color:green;">${response.data.message} <u><a href="">Reload Window</a></u></span>`
                );
                t.find("button").text("Delete");
              }
            },
            error: function (error) {
              console.error("Error:", error);
            },
            complete: function () {
              isAjaxRunning = false;
            },
          });
        }
    });
  }
  delete_data_ajax(".existing_candidate_delete", "delete_existing_candidate");
  delete_data_ajax(
    ".existing_company_delete",
    "delete_existing_posttypedata",
    "company"
  );
  delete_data_ajax(
    ".existing_job_delete",
    "delete_existing_posttypedata",
    "jobs"
  );
  delete_data_ajax(
    ".existing_email_delete",
    "delete_existing_posttypedata",
    "email-log"
  );
  delete_data_ajax(
    ".existing_notification_delete",
    "delete_existing_posttypedata",
    "notification"
  );

  //   prevent browser reload
  $(window).on("beforeunload", function () {
    if (isAjaxRunning) {
      return "❗⚠️ Deletion is in progress. If you leave this page, the operation might be interrupted and cause issues.";
    }
  });
});
