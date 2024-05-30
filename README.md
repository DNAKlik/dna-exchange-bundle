# Hello DnaExchangeBundle!

DnaExchangeBundle is a way to provide dna stamps to content and users.
With this dna the bundle can provide related content or related users.

Install the package with:

```console
composer require dnaklik/dna-exchange-bundle
```

And... that's it! If you're *not* using Symfony Flex, you'll also
need to enable the `DnaKlik\DnaExchangeBundle\DnaKlikDnaExchangeBundle`
in your `AppKernel.php` file.

## Usage

To provide content ans users with dna stamps add this code in your controller. If there is no user logged in the DNA stamps are stored in a session.

```php
// src/Controller/SomeController.php

use DnaKlik\DnaExchangeBundle\Service\DnaKlikExchange;
use Symfony\Component\HttpFoundation\Request;
// ...

class SomeController
{
    
    public function __construct(Request $request, DnaKlikExchange $dnaKlikExchange, DnaExchangeContentRepository $dnaExchangeContentRepository)
    {
        $this->dnaKlikExchange = $dnaKlikExchange;
        $this->dnaExchangeContentRepository = $dnaExchangeContentRepository;
    }
    
    public function detail()
    {
        $stamp = $this->dnaKlikExchange->getStamp($request);

        // ...
    }
}

## To find related content:
    public function relatedContent(Request $request): response
    {
        $items = $this->dnaKlikExchange->getRelatedContent($request, 30);
        foreach($items as $values) {
            $values["slug"];
            $content = $this->doctrine
                ->getRepository(Content::class)
                ->findOneBy(array("urlName" => $slugParts[2]));
            $values["matchCorr"];
            foreach($values["stamps"] as $stamp => $stampCount) {
                if (isset($values["matchStamps"][$stamp])) {
                    $dna .= "<span class='match'>".$stamp." (".$stampCount.")</span> ";
                }
                else {
                    $dna .= $stamp." (".$stampCount.") ";
                }
            }
            $values["totalStampCount"]);
        }
    }

## An array with slugs is returned. You can use the slugs to retrieve the related content from your own application
```

You can also access this service directly using the id
`dnaklik_dna_exchange.dnaklik_exchange`.

## Configuration

Some optional parameters can be configured directly by
creating a new `config/packages/dnaklik_dna_exchange.yaml` file. The
default values are:

```yaml
# config/packages/dnaklik_dna_exchange.yaml
# crossover value
crossOver:    8

# max number off stamps per item
maxStamps: 64

stamp_provider: App\Service\CustomDnaKlikStampProvider
```

## Extending the Stamp List

If you're feeling *especially* creative and excited, you can customize
dna_exchange to provide it with extra content!

To do that, create a class that implements `DnaKlikStampProvider`:
Example of your own stampprovider with content from colors

```php
namespace App\Service;

use DnaKlik\DnaExchangeBundle\Service\DnaKlikStampProvider;

class CustomDnaKlikStampProvider extends DnaKlikStampProvider
{
    public function getContent(): array
    {
        $result = $this->dnaExchangeContentStampRepository->StampsInContent();
        foreach($result as $index => $content) {
            $slugParts = explode("/", $content["slug"]);
            $color = $this->manager
                ->getRepository(Color::class)
                ->findOneBy(array("urlName" => $slugParts[2]));
            $result[$index]["property"] = $color->getColor();
        }
        return $result;
    }

    function getContentFromId($id) {
        $result = $this->dnaExchangeContentRepository->findBy(array("id" => $id),array('id'=>'DESC'),3,0);
        foreach($result as $index => $content) {
            $slugParts = explode("/", $content->getSlug());
            $color = $this->manager
                ->getRepository(Color::class)
                ->findOneBy(array("urlName" => $slugParts[2]));
        }
        return $color;
    }

    public function findMatchItems($dna, $max)
    {
        $items = $this->matchDna->findMatchItems($dna, $max);
        foreach($items as $index => $item) {
            $slugParts = explode("/", $item["slug"]);
            $color = $this->manager
                ->getRepository(Color::class)
                ->findOneBy(array("urlName" => $slugParts[2]));
            $items[$index]["property"] = $color->getColor();
        }
        // dump($items);
        return $items;
    }
}
```

There is also an admin to evaluate the progress of the dna exchange:
The route to this admin can be configured by
creating a new `config/routes/dnaklik_dna_exchange.yaml` file. 

```yaml
_dna_exchange:
  resource: '@DnaKlikDnaExchangeBundle/Resources/config/routes.xml'
  prefix: /dna
```

The url to this admin is: <your_url>/dna/exchange/admin

## Contributing

Of course, open source is fueled by everyone's ability to give just a little bit
of their time for the greater good. If you'd like to see a feature or add some of
your *own* happy words, awesome! Tou can request it - but creating a pull request
is an even better way to get things done.

Either way, please feel comfortable submitting issues or pull requests: all contributions
and questions are warmly appreciated :).
