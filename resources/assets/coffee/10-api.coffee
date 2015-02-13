# api functions
api = do ()->
    api = {}

    # ###################################################
    # Internal Functions

    signPublicRequest = (xhr, xhrOptions)->
        xhr.setRequestHeader('X-Tokenly-Auth-Api-Token', window.quoteBotAPIToken)

        return

    # ###################################################
    # Api

    api.allQuotes = ()->
        # this is to see if we are logged in successfully
        return api.send('GET', 'quote/all')

    api.send = (method, apiPathSuffix, params=null, additionalOpts={})->
        path = '/api/v1/'+apiPathSuffix

        opts = {
            method: method,
            url: path,
            data: params,
            config: signPublicRequest,
            # background: true,
        }

        # merge additionalOpts
        opts[k] = v for k, v of additionalOpts

        return m.request(opts)

    return api
