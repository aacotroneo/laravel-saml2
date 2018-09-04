<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Aacotroneo\Saml2\ExtractOpenssl;

class ExtractOpensslTest extends TestCase
{
    protected $expectedCert = 'MIIC5TCCAc2gAwIBAgIJAObIWyE4hypEMA0GCSqGSIb3DQEBCwUAMBQxEjAQBgNVBAMMCXRlc3QudGVzdDAeFw0xODA4MzAxNDQxMzZaFw0xODA5MjkxNDQxMzZaMBQxEjAQBgNVBAMMCXRlc3QudGVzdDCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBALj5bNdP684tFwBgYgl7Sr0eOaH8NexrQasghTynYm0E/y5voj7gHYQm+hf5ZbzKtWFnBugyJfbhMxtx7Df3j/PYjWw5S/I/QWzwzw/d7MEpBqEDy8XPjmnIpqzlEdeFpbdCTqvguTm+OKJ4JQski34WitRn8OKj+W0COuuU5xqcj84aIlfIojeOuSi8FSb8yrASCO1RAUfJHOtpiZ/IUmznKMrUduYP4Fa4jM5WTBPSoZWYMP5yBO9STsR2wzWIgeVDAs/xGs7/LjVxGHN76jTKpPy+0SX7aySA49zqRTq8ECm2dX/d+vOXJUu22pNFPKlwSFjJkBbL2VrpqS6KymECAwEAAaM6MDgwFAYDVR0RBA0wC4IJdGVzdC50ZXN0MAsGA1UdDwQEAwIHgDATBgNVHSUEDDAKBggrBgEFBQcDATANBgkqhkiG9w0BAQsFAAOCAQEAtqpKZ5KqymKeLNKs8dcIt5MyhqK5hLDfSLMGZp7MufT+YdsEi+Qw0bHNwV4d9akt9Horf7L49YzyN+XZs82Wn8Ca1n51++6QWtCu5DpYKQQ1c4R4QbhRVufJgBIrNhj3fD88R9QB2BjEBsmh+s71rvLB2Ujp3E/PMb2HeM/EXiSCYU5aUJIH1PBjZ7jNlDLDibiviQz2dMrLmQgzeU/Fai1H4Bh7wGSu/tiIxrwnIQVIFLVJSwFI7susx5PRfiE/TD8qrz1ZaRx3bwOEBrsR/1c1Rd5SBhHLHXkJj+BLfOhKVRbYMQhHqMp8zOzBP+o69zrTcSa5O0/xfsPkKulZgw==';

    protected $expectedKey = 'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC4+WzXT+vOLRcAYGIJe0q9Hjmh/DXsa0GrIIU8p2JtBP8ub6I+4B2EJvoX+WW8yrVhZwboMiX24TMbcew394/z2I1sOUvyP0Fs8M8P3ezBKQahA8vFz45pyKas5RHXhaW3Qk6r4Lk5vjiieCULJIt+ForUZ/Dio/ltAjrrlOcanI/OGiJXyKI3jrkovBUm/MqwEgjtUQFHyRzraYmfyFJs5yjK1HbmD+BWuIzOVkwT0qGVmDD+cgTvUk7EdsM1iIHlQwLP8RrO/y41cRhze+o0yqT8vtEl+2skgOPc6kU6vBAptnV/3frzlyVLttqTRTypcEhYyZAWy9la6akuisphAgMBAAECggEALHhq3mjsfCkC+qgxaa4mjckSegs0u54dr5Kl9asYUrV81CEqlIs1DWyBe/oNp5HkgYJPestzrSL/Mn31GI+AIFPTzE0KITdr91D9twbXwKioW1WaS/hWeMAwsihwXaxX5vMeDtx8K5G78/OGlGM41ht9TQugMhzR/o8mszSdSxwn0xHQOC0Agg09G4gDwa/ECwAWdkrXHcGpziXg9Qj5ZLGkbmOJBLOVuWvpbE8oyuijKBIssxR1AHEr3YbIhcSzfO/IIPGanYcJ/uPXphZxxVvFlmcj29X1rL6LiyL0EOv6Re7/7aG8u28Mnjkc4B0dVcSZ0BdD2WmtYh5wP1ToQQKBgQDrPDKrp3m8bn0aNSBol5WQxVjhhN/CF78LWR1BCxzn7C9bxOXnM6qNYIyVrgUKuVJ5pvmUbpY+2YcykzrrIGLSFPA6y2oPEeXFcAER9WKjYmNto8BsrGqoSPIiZOB7D7wntaC97dCdnCE+RvDe+pSJ4K9lqkst0WpMQL65upXuhQKBgQDJTXEgWSKgpncV8BSRsPJHdICWgQ2HDuGWF2eoyQVjptGE4e1mrwGnPR2OW2KGZ8zxIhInO4vVV+tGlnJnP8Lx4hPq5/fgqeZmI+eh/CXB2m/gjJW4VH2xuJa+tkJYTjlfjRXyYJEg0N4VE6DSkS8VIkeebgFG5hRL2r1BxJd5LQKBgQCSjK1QrYS09OyxcBmhr5Y5XAk0bnBsXhjiPAFyrTaz8jvK408Li++cJmNPONvhQ3VzXqgsZfzqaODGjFzvcPy/vtWu+102yEKqj03LX2G1Qi2Jd7QAwCWuc8uNy+TiJfpljsz2pnsKReOcBdw4Pkpd34HGR6KQh9++Y7Oux+RydQKBgAfSxam/LRRXQ9uLaBE9cj0KrxCqVU9Bacz+fd3Waio0SoJCkYpjFMpeGq70qECW+iUI8PGrY8TX1OH6aNnQZZAm/CUt/LkzgSvJC3CFLyZ4ic6NSChQyE3G4bzpsmxiJeKrxgWUcS94Tpk9GQv17oGAwo3KsqwBtxo3lxFeRZDFAoGBAJC1N8wCNuDRsBlf9ksDkZDqYwQXBLIASCUCR7Xawq5cjWNL5plv/7h6jA+dIjHHtFQuZZYw4WNs716cCU1T6il0mUdUoIn2WVsPt7MD91DtNMg/znH8wTw//IeGO3898t4Gk4sltRblalNNC8mCcedTuDyTcz46fbEGYoJdxguv';

    public function testExtractCert()
    {
        $extractedCert = ExtractOpenssl::certFromFile(__DIR__.'/Fixtures/test.crt');
        
        $this->assertNotEmpty($extractedCert);
        $this->assertEquals($this->expectedCert, $extractedCert);
    }
    
    public function testExtractPrivatekey()
    {
        $extractedKey = ExtractOpenssl::privatekeyFromFile(__DIR__.'/Fixtures/test.key');
        
        $this->assertNotEmpty($extractedKey);
        $this->assertEquals($this->expectedKey, $extractedKey);
    }
}
 