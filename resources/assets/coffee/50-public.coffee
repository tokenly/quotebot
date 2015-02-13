
newPusherClient = ()->
    client = new window.Faye.Client("#{window.PUSHER_URL}/public")
    return client

subscribeToPusherChannel = (client, channelName, callbackFn)->
    client.subscribe "/#{channelName}", (data)->
        # console.log "data=",data
        callbackFn(data)
        return
    return client

closePusherChannel = (client)->
    client.disconnect()
    return

handleQuoteUpdate = (newQuote)->
    # console.log "handleQuoteUpdate newQuote=",newQuote
    quotes = vm.quotes()
    found = false

    updatedQuotes = []
    for existingQuote in quotes
        if newQuote.source == existingQuote.source and newQuote.pair == existingQuote.pair
            if newQuote.last == existingQuote.last
                newQuote.direction = 'none'
            else
                if newQuote.last > existingQuote.last
                    # up
                    newQuote.direction = if existingQuote.direction == 'up' then 'up2' else 'up'
                else
                    # down
                    newQuote.direction = if existingQuote.direction == 'down' then 'down2' else 'down'
            updatedQuotes.push(newQuote)
            found = true
        else
            # don't change direction
            updatedQuotes.push(existingQuote)

    if not found
        updatedQuotes.push(newQuote)

    vm.quotes(updatedQuotes)

    # this is outside of mithril, so we must force a redraw
    m.redraw(true)

    return



vm = do ()->
    vm = {}

    vm.init = ()->
        # console.log "vm.init"

        vm.quotes = m.prop([])



        api.allQuotes().then(
            (quoteData)->
                # console.log "quoteData", quoteData
                quotes = []
                client = newPusherClient()
                for k, quote of quoteData.quotes
                    quotes.push({
                        source: quote.source
                        pair: quote.pair
                        last: quote.last
                        lastAvg: quote.lastAvg
                        lastLow: quote.lastLow
                        lastHigh: quote.lastHigh
                        time: quote.time
                        inSatoshis: quote.inSatoshis
                        direction: 'up'
                    })

                    channelName = "quotebot_quote_#{quote.source}_#{quote.pair.replace(':','_')}"
                    # console.log "subscribeToPusherChannel /#{channelName}"
                    subscribeToPusherChannel(client, channelName, handleQuoteUpdate)

                vm.quotes(quotes)

                return
            , (errorResponse)->
                vm.errorMessages(errorResponse.errors)
                return
        )


    return vm

ctrl = ()->
    vm.init()

    return

view = ()->
    return m("div", {style:{marginTop: '28px'}}, [
        m("div", {class: "row"}, vm.quotes().map((quote)->
            return m("div", {class: "col-md-4"}, [
                m("div", {class: "panel panel-default price-panel"}, [
                    m("div", {class: "panel-heading"}, [
                        m("div", {class: "currency"}, quote.pair),
                        m("div", {class: "source"}, quote.source),
                    ]),
                    m("div", {class: "panel-body"}, [
                        m("div", {class: "values direction-#{quote.direction}"}, [
                            m("div", {class: "price priceCurrent"}, [
                                m("div", {class: "value"}, [
                                    m("span", {}, quote.last),
                                    m("span", {class: "satoshis"}, if quote.inSatoshis then "satoshis" else ''),
                                ]),
                                # m("div", {class: "priceLabel"}, "Last"),
                            ]),
                            m("div", {class: "price price24"}, [
                                m("div", {class: "value"}, quote.lastHigh),
                                m("div", {class: "priceLabel"}, "24hr. High"),
                            ]),
                            m("div", {class: "price price24"}, [
                                m("div", {class: "value"}, quote.lastLow),
                                m("div", {class: "priceLabel"}, "24hr. Low"),
                            ]),
                            m("div", {class: "price price24"}, [
                                m("div", {class: "value"}, quote.lastAvg),
                                m("div", {class: "priceLabel"}, "24hr. Avg"),
                            ]),
                        ]),
                        " ",
                    ]),
                    m("div", {class: "panel-footer"}, [
                        m("small", {}, window.moment(quote.time).format('h:mm:ss a')),
                    ]),
                ]),
            ])
        )),
    ])

m.module(document.getElementById('Quotes'), {controller: ctrl, view: view})

###
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
###