jQuery(document).ready(function ($) {
  // ===
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

  // upload all csv file
  $(document).on("submit", ".upload_all_file_form", function (e) {
    e.preventDefault();
    var t = $(this);
    const files = t.find('input[name="upload_allcsv_file[]"]')[0].files;
    const formData = new FormData();
    $.each(files, function (i, file) {
      formData.append("upload_allcsv_file[]", file);
    });
    formData.append("action", "upload_all_file_handler");
    t.find(".reloading_text").html(
      `<span style="color:red;">Save in Progress... This will take time, Don't Close this window or browser</span>`
    );
    t.find("button").text("Saving...");
    isAjaxRunning = true;
    $.ajax({
      url: dataAjax.ajaxurl,
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.success) {
          t.find(".reloading_text").html(
            `<span style="color:green;">File Saved Successfully on server <u><a href="">Reload Window</a></u></span>`
          );
          t.find("button").text("Save File to Server");
          isAjaxRunning = false;
          location.reload();
        }
      },
      error: function (error) {
        console.error("Error:", error);
      },
      complete: function () {
        isAjaxRunning = false;
      },
    });
  });
  $(document).on("submit", ".delete_all_file_form", function (e) {
    e.preventDefault();
    var t = $(this);
    t.find(".reloading_text").html(
      `<span style="color:red;">Delete CSV Folder in Progress... This will take time, Don't Close this window or browser</span>`
    );
    t.find("button").text("Deleting...");
    isAjaxRunning = true;
    $.ajax({
      url: dataAjax.ajaxurl,
      type: "POST",
      data: {
        action: "delete_csv_file_handler",
      },
      success: function (response) {
        if (response.success) {
          t.find(".reloading_text").html(
            `<span style="color:green;">File Deleted Successfully <u><a href="">Reload Window</a></u></span>`
          );
          t.find("button").text("Delete CSV Folder");
          isAjaxRunning = false;
          location.reload();
        }
      },
      error: function (error) {
        console.error("Error:", error);
      },
      complete: function () {
        isAjaxRunning = false;
      },
    });
  });

  // test csv button
  $(document).on("click", ".test_csv_btn", function (e) {
    e.preventDefault();
    var t = $(this);
    var csv_file = $("#csv_file");
    var csv_viewer = $(".csv_viewer");
    var csv_file_name = csv_file.val();
    t.parent()
      .find(".reloading_text")
      .html(
        `<span style="color:red;">Processing... This will take time, Don't Close this window or browser</span>`
      );
    t.text("Processing...");
    $.ajax({
      url: dataAjax.ajaxurl,
      type: "POST",
      data: {
        action: "show_csv_handler",
        csv_file_name: csv_file_name,
      },
      success: function (response) {
        if (response.success) {
          // Clear the reloading text and reset button text
          t.parent().find(".reloading_text").html("");
          t.text("Test Csv");

          // Get the data from the response
          var csvData = response.data.csv_data;

          // Format the CSV data as a table
          var csvHtml =
            "<table style='width:100%; border-collapse: collapse;'><thead><tr>";

          // Add the column numbers in the first row
          csvHtml += "<th style='border: 1px solid #ddd; padding: 8px;'>#</th>"; // Empty cell for row number

          if (csvData.length > 0) {
            // Add column numbers (1, 2, 3, ...)
            Object.keys(csvData[0]).forEach(function (key, index) {
              csvHtml +=
                "<th style='border: 1px solid #ddd; padding: 8px;'>" +
                (index + 1) +
                "</th>";
            });
            csvHtml += "</tr><tr>";

            // Add the column names in the second row (no gap in the first column)
            // Start with the row number in the first column
            csvHtml +=
              "<td style='border: 1px solid #ddd; padding: 8px;'>1</td>"; // Row number for the column names

            // Add the column names for each column
            Object.keys(csvData[0]).forEach(function (key) {
              csvHtml +=
                "<th style='border: 1px solid #ddd; padding: 8px;'>" +
                key +
                "</th>";
            });

            csvHtml += "</tr></thead><tbody>";

            // Create table rows with data
            csvData.forEach(function (row, rowIndex) {
              csvHtml += "<tr>";
              // Add the row number (1, 2, 3, ...)
              csvHtml +=
                "<td style='border: 1px solid #ddd; padding: 8px;'>" +
                (rowIndex + 2) +
                "</td>"; // Start row number from 2

              // Add the row data (values)
              Object.values(row).forEach(function (value) {
                csvHtml +=
                  "<td style='border: 1px solid #ddd; padding: 8px;'>" +
                  value +
                  "</td>";
              });
              csvHtml += "</tr>";
            });
            csvHtml += "</tbody></table>";
          } else {
            csvHtml = "<p>No data available in the CSV.</p>";
          }

          // Insert the formatted HTML into the csv_viewer
          var csvViewer = $(".csv_viewer");
          csvViewer.html(csvHtml);
          csvViewer.show();
        }
      },
      error: function (error) {
        console.error("Error:", error);
      },
    });
  });
});
