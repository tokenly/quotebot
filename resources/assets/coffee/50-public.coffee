console.log "hello world"

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
    console.log "handleQuoteUpdate newQuote=",newQuote
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
                        bid: quote.bid
                        last: quote.last
                        ask: quote.ask
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
                        quote.source
                    ]),
                    m("div", {class: "panel-body"}, [
                        m("div", {class: "price direction-#{quote.direction}"}, quote.last),
                        " ",
                        m("span", {class: "currency"}, quote.pair),
                        if quote.inSatoshis then m("span", {class: "satoshis"}, "satoshis") else null,
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