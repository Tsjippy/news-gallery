import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import apiFetch from "@wordpress/api-fetch";
import { useState, useEffect } from "@wordpress/element";
import { ServerSideRender, useServerSideRender } from '@wordpress/server-side-render';
import { RawHTML } from '@wordpress/element';
import { store as coreDataStore } from "@wordpress/core-data";
import { useSelect } from "@wordpress/data";
import {
  Panel,
  PanelBody,
  PanelRow,
  Spinner,
  CheckboxControl,
  ToggleControl,
  __experimentalNumberControl as NumberControl,
  __experimentalInputControl as InputControl,
  ColorPicker,
} from "@wordpress/components";

const Edit = ({ setAttributes, attributes, context }) => {
  const { postTypes, amount, categories, title, color, gradient } = attributes;
  const curPostType = context["postType"];

  /**
   * Load all post types and their taxonomies
   */
  const [availablePostTypes, setAvailablePostTypes]   = useState([]); 
  const [availableCategories, setAvailableCategories] = useState({}); 

  useEffect(() => {
    apiFetch({ path: "/wp/v2/types?public=true" }).then( res => {
      // Do not keep the post types in this array
      let postTypes = Object.values(res).filter(
        (type) =>
          ![
            "nav_menu_item",
            "wp_block",
            "wp_template",
            "wp_template_part",
            "wp_navigation",
            "wp_global_styles",
            "wp_font_family",
            "wp_font_face"
          ].includes(type.slug),
      );

      setAvailablePostTypes(postTypes);

      /**
       * Get the categories for each post type
       */
      let processedTax  = [];
      postTypes.forEach((type) => {
        let cats = availableCategories;
          if (cats[type.slug] == undefined) {
            cats[type.slug] = {};
          }

          type.taxonomies.forEach((taxonomy) => {
            if(taxonomy == 'category'){
              taxonomy = 'categories';
            } else if(taxonomy == 'post_tag'){
              taxonomy = 'tags';
            }

            // We don't have to do this more than once
            if(processedTax.indexOf(taxonomy) > -1){
              return;
            }

            processedTax.push(taxonomy);

            apiFetch({ path: `/wp/v2/${taxonomy}/?per_page=100`, }).then(res => {
              let cats = availableCategories;
              
              cats[type.slug][taxonomy] = res;

              setAvailableCategories(cats);
            });
          });
        });
    });
  }, []);

  const onPostTypeSelect = function (slug, checked) {
    let selPostTypes  = [...postTypes]
    if (!checked) {
      selPostTypes = selPostTypes.filter((el) => el != slug);
    } else {
      selPostTypes.push(slug);
    }

    setAttributes({ postTypes: selPostTypes });
  };

  const onCategorySelect = function (type, tax, slug, checked) {
    let selCategories = { ...categories };

    if (selCategories[type] == undefined) {
      selCategories[type] = {};
    }

    if (selCategories[type][tax] == undefined) {
      selCategories[type][tax] = [];
    }

    if (!checked) {
      selCategories = selCategories[type][tax].filter((el) => el != slug);
    } else if (!selCategories[type][tax].includes(slug)) {
      selCategories[type][tax].push(slug);
    }

    setAttributes({ categories: selCategories });
  };

  // build the checkboxes for the post type selections
  const getPostTypeCheckboxes = () => {
    if(availablePostTypes.length == 0){
      return [
        <br></br>,
        "Loading..."
      ];
    }

    return availablePostTypes.map((c) => (
      <CheckboxControl
        label    = {c.name}
        onChange = {(checked) => { onPostTypeSelect(c.slug, checked); }}
        checked  = {postTypes.includes(c.slug)}
        key      = {c.slug}
      />
    ))
  }

  // build the checkboxes for the category selection
  const getCategoryTypeCheckboxes = () => {
    if(Object.keys(availableCategories).length == 0){
      return [
        <br></br>,
        "Loading..."
      ];
    }

    if(postTypes.length == 0){
      return "Select a post type first...";
    }

    let selected = true;
    let rendered = ["Select the categories you want from any post type. Leave empty for all", <br></br>];

    /**
     * Show the categories for each selected post type
     */
    postTypes.forEach((postType) => {
      rendered.push(
        <h2>{postType.charAt(0).toUpperCase() + postType.slice(1)}</h2>,
      );

      Object.keys(availableCategories[postType]).forEach((tax) => {
        rendered.push(tax.charAt(0).toUpperCase() + tax.slice(1));

        Object.values(availableCategories[postType][tax]).map((c) => {
          selected = true;
          try {
            selected = categories[postType][tax].includes(c.slug);
          } catch (e) {
            selected = false;
          }

          rendered.push(
            <CheckboxControl
              label    = {c.name}
              onChange = {(checked) => { onCategorySelect(postType, tax, c.slug, checked); }}
              checked  = {selected}
              key      = {c.id}
            />,
          );
        });
      });
    });

    return rendered;
  }

  const getServerSideRenderedContent = ( ) => {
    const { content, status, error } = useServerSideRender( {
        block: "tsjippy-news-gallery/show",
        attributes: attributes,
        urlQueryArgs: { context: 'edit' } // Optional custom query arguments
    } );

    const blockProps = useBlockProps();

    let html;

    if ( status === 'loading' ) {
        html = "Loading...";
    }

    else if ( status === 'error' ) {
        html = `Error: ${ error }`;
    }

    else{
      html  = <RawHTML>{ content }</RawHTML>; 
    }

    return <div {...blockProps}>
      { html }
    </div>;
  }

  return (
    <>
      <InspectorControls>
        <Panel>
          <PanelBody title="Properties" initialOpen={true}>
            <InputControl
              label                = {__("Title", "tsjippy")}
              isPressEnterToChange = {true}
              value                = {attributes.title}
              onChange             = {(value) => setAttributes({ title: value })}
            />
            {__("How many posts should be shown at once", "tsjippy")}
            <NumberControl
              label    = {__("Posts Amount", "tsjippy")}
              value    = {attributes.amount}
              onChange = {(val) => setAttributes({ amount: parseInt(val) })}
              min      = {1}
              max      = {12}
            />
            {__("Max Time Since Published", "tsjippy")}
            <NumberControl
              label    = {__("Max Age in Days", "tsjippy")}
              value    = {attributes.age}
              onChange = {(val) => setAttributes({ age: parseInt(val) })}
              min      = {1}
              max      = {90}
            />
          </PanelBody>
          <PanelBody title="Background Color" initialOpen={false}>
              <ColorPicker
                color        = {color}
                onChange     = {(color) => setAttributes({ color: color })}
                enableAlpha
                defaultValue = "#000"
              />

              <ToggleControl
                  label    = {__("Use Background Gradient", "tsjippy")}
                  checked  = { gradient }
                  onChange = {(checked) => setAttributes({ gradient: checked })}
                />
          </PanelBody>
          <PanelBody title="Post Types" initialOpen={false}>
            Select the post types you want to include in the gallery:
            { getPostTypeCheckboxes() }
          </PanelBody>
          <PanelBody title="Categories" initialOpen={false}>
            { getCategoryTypeCheckboxes() }
          </PanelBody>
        </Panel>
      </InspectorControls>
      { getServerSideRenderedContent() }
    </>
  );
};

export default Edit;
