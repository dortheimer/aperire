define(['jquery', 'onsenui'], function ($, ons) {

  return {
    get: function (path, params) {
      return new Promise(function (resolve, reject) {
        $.ajax({
            data: params,
            url: path,
          })
          .done(function (result) {
            resolve(result.data);
          })
          .fail(function (jqXHR, textStatus, errorThrown) {
            ons.notification.alert('Server returned an error:' + errorThrown);
            reject(exception);
          })
      })
    },
    post: function (path, params) {
      return new Promise(function (resolve, reject) {
        $.ajax({
            type: "POST",
            data: params,
            url: path,
          })
          .done(function (result) {
            resolve(result.data);
          })
          .fail(function (jqXHR, textStatus, errorThrown) {
            ons.notification.alert('Server returned an error:' + errorThrown);
            reject(exception);
          })
      })
    }
  }
});