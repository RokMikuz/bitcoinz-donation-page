<?php

const BTCZ_ADDRESSES = [
    't1fHHnAXxoPWGY77sG5Zw2sFfGUTpW6BcSZ',
    't1L7TtcRPKztgScLnfUToe4sa2aFKf9rQ14',
    't3hTi3fXhcjgjRktoiucUKRtDXxV4GfEL1w',
];
const ETH_ADDRESS = '0x4E3154bc8691BC480D0F317E866C064cC2c9455D';
const BTC_ADDRESS = '1BzBfikDBGyWXGnPPk58nVVBppzfcGGXMx';
const ZEC_ADDRESS = 't1ef9cxzpToGJcaSMXbTGRUDyrp76GfDLJG';
const LTC_ADDRESS = 'LR8bPo7NjPNRVy6nPLVgr9zHee2C7RepKA';
const USDTE_ADDRESS = '0xD36591b20f738f6929272a4391B8C133CB2e5C96';


const CACHE_TEMPLATE = __DIR__ . '/cache/%s.cache';

function getCache($key) {
    $cache_file = sprintf(CACHE_TEMPLATE, $key);
    if (file_exists($cache_file) && (filemtime($cache_file) > (time() - 60))) {
        return file_get_contents($cache_file);
    }

    return false;
}

function setCache($key, $value) {
    file_put_contents(
        sprintf(CACHE_TEMPLATE, $key),
        $value,
        LOCK_EX
    );
}


function getCoinPrice($coin) {

    #if ($cache = getCache($coin.'-price')) {
    #    return $cache;
    #}

    $data = file_get_contents('https://api.coingecko.com/api/v3/simple/price?ids='.$coin.'&vs_currencies=usd');
    if ($data === false) {
        return null;
    }


    $data = json_decode($data);
    #setCache($coin.'-price', $data->bitcoinz->usd);

    return (float)($data->$coin->usd);
}

function getBtczBalance()
{
    if ($cache = getCache('btcz-balance')) {
        return $cache;
    }

    $total = 0;
    foreach (BTCZ_ADDRESSES as $address) {
        $addressTotal = file_get_contents('http://btczexplorer.blockhub.info/ext/getbalance/' . $address);


        $total += $addressTotal;
    }

    setCache('btcz-balance', $total);

    return $total;
}

function getEthBalance()
{
    return 0;
    if ($cache = getCache('eth-balance')) {
        return $cache;
    }

    $data = file_get_contents('https://etherchain.org/api/account/' . ETH_ADDRESS);
    if ($data === false) {
        return null;
    }

    $data = json_decode($data);
    if ($data->status !== 1) {
        return null;
    }

    setCache('eth-balance', $data->data[0]->balance / 1000000000000000000);

    return $data->data[0]->balance / 1000000000000000000;
}

function getBtcBalance()
{
    if ($cache = getCache('btc-balance')) {
        return $cache;
    }

    $data = file_get_contents('http://blockchain.info/q/addressbalance/' . BTC_ADDRESS);
    if ($data === false) {
        return null;
    }

    $data = $data/100000000;

    setCache('btc-balance', $data);

    return $data;
}

function getZecBalance()
{
    if ($cache = getCache('zec-balance')) {
        return $cache;
    }

    $data = file_get_contents('https://api.zcha.in/v2/mainnet/accounts/' . ZEC_ADDRESS);

    if (!$data) {
        return null;
    }

    $data = json_decode($data);
    setCache('zec-balance', $data->balance);

    return $data->balance;
}

function getLtcBalance()
{
    if ($cache = getCache('ltc-balance')) {
        return $cache;
    }

    $data = file_get_contents('https://chainz.cryptoid.info/ltc/api.dws?q=getbalance&a=' . LTC_ADDRESS);
    if ($data === false) {
        return null;
    }

    setCache('ltc-balance', $data);

    return $data;
}
function getUSDTEBalance()
{
    if ($cache = getCache('USDTE-balance')) {
        return $cache;
    }

    $data = explode(',\"', explode('balance_usd\":',(json_encode(file_get_contents('https://api.blockchair.com/ethereum/dashboards/address/' . USDTE_ADDRESS))))[1])[0];

    if ($data === false) {
        return null;
    }

    setCache('USDTE-balance', $data);

    return $data;
}
$response = [
    'btczBalance' => getBtczBalance(),
    'ethBalance' => getEthBalance(),
    'btcBalance' => getBtcBalance(),
    'zecBalance' => getZecBalance(),
    'ltcBalance' => getLtcBalance(),
    'USDTEBalance' => getUSDTEBalance(),
    'btczUsd' => getCoinPrice('bitcoinz'),
    'ethUsd' => getCoinPrice('ethereum'),
    'btcUsd' => getCoinPrice('bitcoin'),
    'zecUsd' => getCoinPrice('zcash'),
    'ltcUsd' => getCoinPrice('litecoin'),
    'USDTEUsd' => getCoinPrice('tether')
];

echo json_encode($response);
