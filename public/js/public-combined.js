(function() {
  var api, closePusherChannel, ctrl, handleQuoteUpdate, newPusherClient, subscribeToPusherChannel, view, vm;

  api = (function() {
    var signPublicRequest;
    api = {};
    signPublicRequest = function(xhr, xhrOptions) {
      xhr.setRequestHeader('X-Tokenly-Auth-Api-Token', window.quoteBotAPIToken);
    };
    api.allQuotes = function() {
      return api.send('GET', 'quote/all');
    };
    api.send = function(method, apiPathSuffix, params, additionalOpts) {
      var k, opts, path, v;
      if (params == null) {
        params = null;
      }
      if (additionalOpts == null) {
        additionalOpts = {};
      }
      path = '/api/v1/' + apiPathSuffix;
      opts = {
        method: method,
        url: path,
        data: params,
        config: signPublicRequest
      };
      for (k in additionalOpts) {
        v = additionalOpts[k];
        opts[k] = v;
      }
      return m.request(opts);
    };
    return api;
  })();

  newPusherClient = function() {
    var client;
    client = new window.Faye.Client(window.PUSHER_URL + "/public");
    return client;
  };

  subscribeToPusherChannel = function(client, channelName, callbackFn) {
    client.subscribe("/" + channelName, function(data) {
      callbackFn(data);
    });
    return client;
  };

  closePusherChannel = function(client) {
    client.disconnect();
  };

  handleQuoteUpdate = function(newQuote) {
    var existingQuote, found, quotes, updatedQuotes, _i, _len;
    quotes = vm.quotes();
    found = false;
    updatedQuotes = [];
    for (_i = 0, _len = quotes.length; _i < _len; _i++) {
      existingQuote = quotes[_i];
      if (newQuote.source === existingQuote.source && newQuote.pair === existingQuote.pair) {
        if (newQuote.last === existingQuote.last) {
          newQuote.direction = 'none';
        } else {
          if (newQuote.last > existingQuote.last) {
            newQuote.direction = existingQuote.direction === 'up' ? 'up2' : 'up';
          } else {
            newQuote.direction = existingQuote.direction === 'down' ? 'down2' : 'down';
          }
        }
        updatedQuotes.push(newQuote);
        found = true;
      } else {
        updatedQuotes.push(existingQuote);
      }
    }
    if (!found) {
      updatedQuotes.push(newQuote);
    }
    vm.quotes(updatedQuotes);
    m.redraw(true);
  };

  vm = (function() {
    vm = {};
    vm.init = function() {
      vm.quotes = m.prop([]);
      return api.allQuotes().then(function(quoteData) {
        var channelName, client, k, quote, quotes, _ref;
        quotes = [];
        client = newPusherClient();
        _ref = quoteData.quotes;
        for (k in _ref) {
          quote = _ref[k];
          quotes.push({
            source: quote.source,
            pair: quote.pair,
            last: quote.last,
            lastAvg: quote.lastAvg,
            lastLow: quote.lastLow,
            lastHigh: quote.lastHigh,
            time: quote.time,
            inSatoshis: quote.inSatoshis,
            direction: 'up'
          });
          channelName = "quotebot_quote_" + quote.source + "_" + (quote.pair.replace(':', '_'));
          subscribeToPusherChannel(client, channelName, handleQuoteUpdate);
        }
        vm.quotes(quotes);
      }, function(errorResponse) {
        vm.errorMessages(errorResponse.errors);
      });
    };
    return vm;
  })();

  ctrl = function() {
    vm.init();
  };

  view = function() {
    return m("div", {
      style: {
        marginTop: '28px'
      }
    }, [
      m("div", {
        "class": "row"
      }, vm.quotes().map(function(quote) {
        return m("div", {
          "class": "col-md-4"
        }, [
          m("div", {
            "class": "panel panel-default price-panel"
          }, [
            m("div", {
              "class": "panel-heading"
            }, [
              quote.source, m("div", {
                "class": "currency"
              }, quote.pair)
            ]), m("div", {
              "class": "panel-body"
            }, [
              m("div", {
                "class": "values direction-" + quote.direction
              }, [
                m("div", {
                  "class": "price priceCurrent"
                }, [
                  m("div", {
                    "class": "value"
                  }, [
                    m("span", {}, quote.last), m("span", {
                      "class": "satoshis"
                    }, quote.inSatoshis ? "satoshis" : '')
                  ])
                ]), m("div", {
                  "class": "price price24"
                }, [
                  m("div", {
                    "class": "value"
                  }, quote.lastHigh), m("div", {
                    "class": "priceLabel"
                  }, "24hr. High")
                ]), m("div", {
                  "class": "price price24"
                }, [
                  m("div", {
                    "class": "value"
                  }, quote.lastLow), m("div", {
                    "class": "priceLabel"
                  }, "24hr. Low")
                ]), m("div", {
                  "class": "price price24"
                }, [
                  m("div", {
                    "class": "value"
                  }, quote.lastAvg), m("div", {
                    "class": "priceLabel"
                  }, "24hr. Avg")
                ])
              ]), " "
            ]), m("div", {
              "class": "panel-footer"
            }, [m("small", {}, window.moment(quote.time).format('h:mm:ss a'))])
          ])
        ]);
      }))
    ]);
  };

  m.module(document.getElementById('Quotes'), {
    controller: ctrl,
    view: view
  });


  /*
   <div class="row">
       <div class="col-md-4">
           <div class="panel panel-default price-panel">
               <div class="panel-heading">bitcoinAverage</div>
               <div class="panel-body">
                   <span class="price">foo</span> <span class="currency">BTC/USD</span>
               </div>
               <div class="panel-footer"><small>time here</small></div>
           </div>
       </div>
       <div class="col-md-4">
           <div class="panel panel-default price-panel">
               <div class="panel-heading">bitcoinAverage</div>
               <div class="panel-body">
                   <span class="price">foo</span> <span class="currency">BTC/USD</span>
               </div>
               <div class="panel-footer"><small>time here</small></div>
           </div>
       </div>
       <div class="col-md-4">
           <div class="panel panel-default price-panel">
               <div class="panel-heading">bitcoinAverage</div>
               <div class="panel-body">
                   <span class="price">foo</span> <span class="currency">BTC/USD</span>
               </div>
               <div class="panel-footer"><small>time here</small></div>
           </div>
       </div>
   </div>
   */

}).call(this);
