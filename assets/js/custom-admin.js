jQuery(document).ready(function ($) {
  // ===
  let isAjaxRunning = false;
  //   prevent browser reload
  $(window).on("beforeunload", function () {
    if (isAjaxRunning) {
      return "❗⚠️ If you leave this page, the operation might be interrupted and cause issues.";
    }
  });

  $(document).on("submit", ".generate_core_folder", function (e) {
    e.preventDefault();
    var t = $(this);
    t.find(".reloading_text").html(
      `<span style="color:red;">Folder Generating... This will take time, Don't Close this window or browser</span>`
    );
    t.find("button").text("Generating...");
    isAjaxRunning = true;
    $.ajax({
      url: dataAjax.ajaxurl,
      type: "POST",
      data: {
        action: "generate_core_folder_handler",
      },
      success: function (response) {
        if (response.success) {
          t.find(".reloading_text").html(
            `<span style="color:green;">${response.data.message} <u><a href="">Reload Window</a></u></span>`
          );
          t.find("button").text("Generate");
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
  $(document).on("submit", ".delete_core_folder", function (e) {
    e.preventDefault();
    var t = $(this);
    t.find(".reloading_text").html(
      `<span style="color:red;">Folder Deleting... This will take time, Don't Close this window or browser</span>`
    );
    t.find("button").text("Deleting...");
    isAjaxRunning = true;
    $.ajax({
      url: dataAjax.ajaxurl,
      type: "POST",
      data: {
        action: "delete_core_folder_handler",
      },
      success: function (response) {
        if (response.success) {
          t.find(".reloading_text").html(
            `<span style="color:green;">${response.data.message} <u><a href="">Reload Window</a></u></span>`
          );
          t.find("button").text("Deleting");
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
    var fileType = t.find('input[name="file_type"]').val();
    var csv_file_name = t
      .closest(".migrate_csv_form")
      .find('select[name="file_name"]')
      .val();
    var csv_viewer = $(".csv_viewer");
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

  // migrate_csv_form
  $(document).on("submit", ".migrate_csv_form", function (e) {
    e.preventDefault();
    var t = $(this);
    var formData = new FormData(this);
    formData.append("action", "migrate_file_handler");

    if (confirm("⚠️ Please backup your database before migrating.")) {
      t.find(".reloading_text").html(
        `<span style="color:red;">Migrating... This will take time, Don't Close this window or browser</span>`
      );
      isAjaxRunning = true;
      $.ajax({
        type: "POST",
        url: dataAjax.ajaxurl,
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
          if (response.success) {
            t.find(".reloading_text").html(
              `<span style="color:green;">Migrated Successfully <u><a href="">Reload Window</a></u></span>`
            );
            isAjaxRunning = false;
            location.reload();
          }
        },
        error: function (error) {
          alert("An unexpected error occurred.");
        },
      });
    }
  });
});
