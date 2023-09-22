<?php

namespace WPML\Core;

use \WPML\Core\Twig\Environment;
use \WPML\Core\Twig\Error\LoaderError;
use \WPML\Core\Twig\Error\RuntimeError;
use \WPML\Core\Twig\Markup;
use \WPML\Core\Twig\Sandbox\SecurityError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedTagError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedFilterError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedFunctionError;
use \WPML\Core\Twig\Source;
use \WPML\Core\Twig\Template;

/* /setup/store-pages.twig */
class __TwigTemplate_379e3d73f3a40a373d3c4f516e3b84b1face36627de19418b77383c4f3c383b8 extends \WPML\Core\Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<span id=\"";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "step_id", []), "html", null, true);
        echo "\">
<h1>";
        // line 2
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "heading", []), "html", null, true);
        echo "</h1>

<p>";
        // line 4
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "description", []), "html", null, true);
        echo "</p>

";
        // line 6
        echo ($context["store_pages"] ?? null);
        echo "

<p class=\"wcml-setup-actions step\">
    <a href=\"";
        // line 9
        echo \WPML\Core\twig_escape_filter($this->env, ($context["go_back_url"] ?? null), "html", null, true);
        echo "\" class=\"go-back\">";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "go_back", []), "html", null, true);
        echo "</a>
    <a href=\"";
        // line 10
        echo \WPML\Core\twig_escape_filter($this->env, ($context["continue_url"] ?? null), "html", null, true);
        echo "\" class=\"button button-primary button-large submit\">";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "continue", []), "html", null, true);
        echo "</a>
</p>
</span>";
    }

    public function getTemplateName()
    {
        return "/setup/store-pages.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  59 => 10,  53 => 9,  47 => 6,  42 => 4,  37 => 2,  32 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("<span id=\"{{ strings.step_id }}\">
<h1>{{ strings.heading }}</h1>

<p>{{ strings.description }}</p>

{{ store_pages|raw }}

<p class=\"wcml-setup-actions step\">
    <a href=\"{{ go_back_url }}\" class=\"go-back\">{{ strings.go_back }}</a>
    <a href=\"{{ continue_url }}\" class=\"button button-primary button-large submit\">{{ strings.continue }}</a>
</p>
</span>", "/setup/store-pages.twig", "/var/www/bike.meihao.shopping/htdocs/wp-content/plugins/woocommerce-multilingual/templates/setup/store-pages.twig");
    }
}
