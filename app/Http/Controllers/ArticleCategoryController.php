<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\articleCategory;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Exception;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ArticleCategoryController extends Controller
{

    // This function returns all categories
    public function index()
    {

    try{

        $CategoryArticle = articleCategory::all();
        return response()->json($CategoryArticle);

    }catch(Exception $e){

        return response()->json([
            'message' => 'Something went wrong. Please try again later.'
        ],400);

    }
    }

    // This function returns all articles with the given category ID
    public function getCategoryByid($id)
    {
        try{
        $CategorySelected = Article::where('category_id', $id)->get();

            if(!$CategorySelected){

                return response()->json([
                    'message' => 'Products by this Category Not found'
                ],404);

            }

            return response()->json($CategorySelected);

    }catch(Exception $e){

        return response()->json([
            'message' => 'Something went wrong. Please try again later.'
        ],400);

    }
    }


    // This function is not used
    public function create()
    {
    }

    // This function creates a new category
    public function store(Request $request)
    {
        try{
            // Check if the category is empty
            if (!$request->filled('category')) {
                return response()->json([
                    'message' => 'Category cannot be empty'
                ],400);
            }

            // Check if the category already exists
            $found = articleCategory::where('category', $request->category)->exists();
            if ($found) {
                return response()->json([
                    'message' => 'Category cannot be duplicated'
                ], 400);
            }

            // Create the new category
            $Added = articleCategory::create([
                'category' => $request->category
            ]);

            // Check if the category was successfully created
            if (!$Added) {
                Log::error('Failed to create category');
                return response()->json([
                    'message' => 'Something went wrong. Please try again later.'
                ], 400);
            }

            // Return a success message and the new category ID
            return response()->json([
                    'message' => 'Category created successfully',
                    'id' => $Added->id
                ]);

        }catch(Exception $e){
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ],400);
        }
    }

    // This function returns a single category by ID
    public function show($id)
    {
        try{
            // Find the category with the given ID
            $FoundedCategory = ArticleCategory::find($id);

            // Check if the category was found
            if (!$FoundedCategory) {
                return response()->json([
                    'message' => 'Category not found'
                ], 404);
            }

            // Return the category data
            return response()->json([
                'Category Requested' => $FoundedCategory
            ],200);

          }catch(Exception $e){
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ],400);
          }
    }


    public function edit($id)
    {


    }


      // Update an article category
      public function update(Request $request, $id)
      {
          try {
              // Check if the category input is not empty
              if (!$request->filled('category')) {
                  return response()->json([
                      'message' => 'Category cannot be empty'
                  ], 400);
              }

              // Find the article category with the given ID
              $categoryFounded = articleCategory::find($id);

              // If the category doesn't exist, return an message
              if (!$categoryFounded) {
                  return response()->json([
                      'message' => 'Category not found'
                  ], 404);
              }

              // Update the category name and save the changes
              $categoryFounded->category = $request->input('category');
              $categoryFounded->save();

              // Return a success message with the updated category
              return response()->json([
                  'message' => 'Category updated successfully',
                  'id' => $categoryFounded
              ]);
          } catch (Exception $e) { DB::rollBack();
            DB::rollBack();
              // Return an message message for any other exceptions
              return response()->json([
                  'message' => 'Something went wrong. Please try again later.'
              ], 400);
          }
      }

      // Delete an article category
      public function destroy($id)
      {
          try {
              // Find the article category with the given ID
              $categoryFounded = articleCategory::find($id);

              // If the category doesn't exist, return an message
              if (!$categoryFounded) {
                  return response()->json([
                      'message' => 'Category not found'
                  ], 404);
              }

              // Delete the category
              $categoryFounded->delete();

              // Return a success message with the deleted category
              return response()->json([
                  'message' => 'Category deleted successfully',
                 'id' => $categoryFounded
              ]);
          } catch (Exception $e) {
             DB::rollBack();
              // Return an message message for any other exceptions
              return response()->json([
                  'message' => 'Something went wrong. Please try again later.'
              ], 400);
          }
      }







}
